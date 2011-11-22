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
 * @version    $Id: Acl.php 248 2010-04-12 19:49:31Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract {
	protected $_options;
	
	public function __construct($options = array()) {
		$this->_options = $options;
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		
		$f = new Zend_Filter_Word_DashToCamelCase();
		$aName = $f->filter($request->getActionName());
		$aName = lcfirst($aName);
		$action = sprintf('/%s/%s/%s',$request->getModuleName(),$request->getControllerName(),$aName);

		//try {
			$resource = Doctrine::getTable($this->_options['action']['class'])->{'findOneBy'.$this->_options['action']['nameProperty']}($action);
		
			if(!$resource) {
				throw new Webir_Exception(sprintf('Resource %s not found.',$action));
			}
			
			$auth = Zend_Registry::get('user')->getAuth();			
			$role = $auth->hasIdentity() ? $auth->getIdentity()->role : Doctrine::getTable($this->_options['role']['class'])->find($this->_options['guestRoleId']);
			
			if(!$role) {
				throw new Webir_Exception('Role not found.');
			}
			if(!$role->isAllowed($resource)) {
				if($auth->hasIdentity()) {
					$request->setModuleName('default');
					$request->setControllerName('error');
					$request->setActionName('forbidden');
					$request->setParam('forbiddenAction',$action);	
				} else {
					$request->setModuleName('default');
					$request->setControllerName('index');
					$request->setActionName('index');
				}
				$request->setDispatched(true);
			}
		//} catch(Doctrine_Exception $e) {
			
		//} catch(Exception $e) {
			
		//}	
	}
}