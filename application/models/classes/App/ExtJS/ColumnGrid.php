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
 * @version    $Id: ColumnGrid.php 351 2010-04-20 09:40:16Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_ExtJS_ColumnGrid extends Webir_ExtJS_Store {
	public function setDefinition() {
		$this->option('aliases',true);
		$this->option('searchFields',array('label','label_short','index'));
	
		$dql = Doctrine_Query::create()->select('c.id, c.index, c.type, c.label, c.label_short, c.description,c.is_ordered','c.segment_id','s.id','s.name')
																			->from('App_R_DataSet_Column c')->leftJoin('c.segment s')
																			->where('data_set_id = ?', (int)$this->getParam('data_set_id'));
		if($this->getParam('subset') == 'true') {
			$dql->select('c.id,c.label,c.segment_id,s.name');
		}	
																			
		if($this->getParam('type') != null) {
			$dql->addWhere('type LIKE ?','%'.$this->getParam('type').'%');
		}
		
		$this->setBaseQuery($dql);
	}
}