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
 * @package    Webir_Controller_Plugin
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Abstract.php 90 2010-04-02 10:26:04Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
abstract class Webir_Configurable_Abstract {
	protected $_options = array();
	
	/**
	 * 
	 * @param array $options
	 * @return void
	 */
	public function __construct(Array $options = array()) {
		$this->_options = array_merge($this->_options,$options);
	}
	
	/**
	 * Get or set option
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 * @throws Webir_Exception
	 */
	public function option($name,$value = null) {
		if($value !== null) {
			$this->_options[$name] = $value;
		}
		
		if($this->hasOption($name)) {
			return $this->_options[$name];
		} else {
			throw new Webir_Exception('Option doesn\'t exist');
		}
	}
	
	/**
	 * Checks if option exist
	 * @param string $key
	 * @return boolean
	 */
	public function hasOption($key) {
		return isset($this->_options[$key]);
	}
}