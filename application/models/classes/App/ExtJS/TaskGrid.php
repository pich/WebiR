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
 * @version    $Id: TaskGrid.php 344 2010-04-19 14:56:25Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_ExtJS_TaskGrid extends Webir_ExtJS_Store {
	public function setDefinition() {
		$dql = Doctrine_Query::create()->select('t.id, t.name, t.status_id, t.created_at, u.email, t.seen_at')->from('App_R_Task t')->leftJoin('t.user u');
		$this->setBaseQuery($dql);
	}
	
	public function load($hydrationMode = Doctrine::HYDRATE_SCALAR) {
		if($this->_params['adminMode'] == false) {
			$this->_dql->addWhere('t.user_id = ? ',$this->_params['t_user_id']);
		}

		return parent::load($hydrationMode);
	}
}