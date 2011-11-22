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
 * @package    App_Auth
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Adapter.php 24 2010-03-20 18:23:14Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_Auth_Adapter extends Webir_Auth_Adapter_Doctrine {
	protected function _validationActivation() {
		$resultClass = $this->option('resultClass');

		if($this->_identity->log_num === null) {
			return new $resultClass(constant($this->option('resultClass').'::FAILURE_ACCOUNT_ACTIVATION'),null,array('Your account has never been activate yet. Check your e-mail account or contact system administrator.'));
		}
		
		return true;
	}
}