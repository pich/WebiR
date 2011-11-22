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
 * @package    App_Controller_Plugin
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Redirect.php 236 2010-04-12 16:50:20Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_Controller_Plugin_Redirect extends Zend_Controller_Plugin_Abstract {
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$redirector = new Zend_Controller_Action_Helper_Redirector();
		$url = new Zend_View_Helper_Url();
		
		$auth = Zend_Registry::get('user')->getAuth();
		
		if ($auth->hasIdentity()) {
			switch(true) {
				// wszystkie akcje z UserController z wyjątkiem wylogowania i konta użytkownika
				case (
					$request->getModuleName() == 'default' &&
					$request->getControllerName() == 'user' &&
					($request->getActionName() != 'signout' && $request->getActionName() != 'account')
				):
				// przekierowanie ze strony głównej
				case $request->getModuleName() == 'default' && $request->getControllerName() == 'index' && $request->getActionName() == 'index':
					$redirector->gotoUrlAndExit($url->url(array(),'welcome'));
			}
		}
	}
}