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
 * @version    $Id: DataSetListGrid.php 305 2010-04-16 10:39:52Z dbojdo $
 */

/**
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class App_ExtJS_DataSetListGrid extends Webir_ExtJS_Store {
	public function setDefinition() {
		$this->option('aliases',false);
		
		$query = Doctrine_Query::create();
		$query->select('id, name, created_at, source_filename')->addWhere('deleted_at IS NULL')->addWhere('deleted_at IS NULL');
		if($this->_request->getParam('mode') == 'normal') {
			$query->addSelect('status_id, format');
		} else {
			// w uproszczonym pokazyjemy tylko te, które są gotowe do użycia
			$query->addWhere('status_id = ?',App_R_DataSet::STATUS_READY);
		}
		
		$query->from('App_R_DataSet_User')->addWhere('user_id = ?', $this->_request->getParam('user_id'));				
		$this->setBaseQuery($query);
	}
}
