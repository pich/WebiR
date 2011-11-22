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
 * @category   Webir
 * @package    Webir_Application_Resource
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Auth.php 24 2010-03-20 18:23:14Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Application_Resource_Auth extends Zend_Application_Resource_ResourceAbstract {
	public function init() {
		die(var_dump($this->_options));
		Zend_Registry::set('auth',$this);
	}
	
	public function getAuth() {
		return call_user_func((isset($this->_options['class']) ? $this->_options['class'] : 'Zend_Auth') . '::getInstance');
	}
	
	public function getAdapter() {
		if(!isset($this->_options['adapter']['class'])) {
			throw new Webir_Exception('Adapter class not found');
		}
		
		$options = isset($this->_options['adapter']['options']) ? $this->_options['adapter']['options'] : array();
		
		return new $this->_options['adapter']['class']($options);
	}
}