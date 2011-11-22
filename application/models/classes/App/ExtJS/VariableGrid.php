<?php

/**
 * WebiR -- The Web Interface to R
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://escsa.eu/license/webir.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to firma@escsa.pl so we can send you a copy immediately.
 *
 * @category   App
 * @package    App_ExtJS
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: VariableGrid.php 377 2010-04-26 12:43:31Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_ExtJS_VariableGrid extends Webir_ExtJS_Store {
	public function setDefinition() {
		$this->option('aliases',true);
		$this->option('searchFields',array('v_label','v_label_short','v_index'));

		$dql = Doctrine_Query::create()->select('v.id, v.type, v.label, v.description, v.position, s.position, s.name, v.segment_id')
																			->from('App_R_DataSet_Column v')
																			->leftJoin('v.segment s')
																			->where('v.data_set_id = ?', (int)$this->getParam('data_set_id'));
																			
		if($this->getParam('type') != null) {
			$dql->addWhere('v.type LIKE ?','%'.$this->getParam('type').'%');
		}
		
		$this->setBaseQuery($dql);

		switch($this->_request->getParam('func')) {
			case 'chi2':
			case 'chart_Bar': 
				$this->_dql->addWhere('v.type = ?',App_R_DataSet_Column::TYPE_FACTOR);
			break;
			case 'regression':
				if($this->_request->getParam('variable') !== '1') {
					$this->_dql->whereIn('v.type',array(App_R_DataSet_Column::TYPE_NUMERIC,App_R_DataSet_Column::TYPE_INTEGER));
				}
			break;
			case 'summary':
			case 'anova':
			case 'wilcoxon':
			case 'kraskal':
			case 'tstudent':
			case 'chart_BarSrednie':
			case 'chart_Box':
			case 'homogeneity':
				if($this->_request->getParam('variable') == App_R_DataSet_Column::TYPE_FACTOR) {
					$this->_dql->whereIn('v.type',array(App_R_DataSet_Column::TYPE_INTEGER,App_R_DataSet_Column::TYPE_NUMERIC));	
				} else {
					$this->_dql->addWhere('v.type = ?',App_R_DataSet_Column::TYPE_FACTOR);
				}
			break;
			case 'chart_Histogram':
			case 'chart_Rozrzut':
			case 'correlation_Nonparam':
			case 'correlation_Param':
			case 'parametric':
				$this->_dql->whereIn('v.type',array(App_R_DataSet_Column::TYPE_INTEGER,App_R_DataSet_Column::TYPE_NUMERIC));
			break;
		}
	}
}