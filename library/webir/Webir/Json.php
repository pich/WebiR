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
 * @package    Webir
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Json.php 251 2010-04-13 10:16:42Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Json {
	protected $_data;
	protected $_success;
	protected $_error;
	protected $_isNode = false;
	
	public function __construct($data=array()) {
		$this->setData($data);
	}
	
	/**
	 * Ustawia własność "_data"
	 * @param mixed $data
	 * @return void
	 */
	public function setData($data) {
		$this->_data = $data;
		$this->success(true);
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function getData() {
		return $this->_data;
	}
	
	/**
	 * Ustawia parametr "_error"
	 * @param mixed $error
	 * @return void
	 */
	public function setError($error) {
		$this->_error = $error;
		$this->success(false);
	}
	
	/**
	 * Sprawdza lub ustawia flagę success
	 * @param boolean|null $flag
	 * @return boolean
	 */
	public function success($flag=null) {
		if(is_bool($flag)) {
			$this->_success = $flag;
		}
		return $this->_success;
	}
	
	/**
	 * Ustawia lub sprawdza flagę "isNode"
	 * Ustawienie tej flagi powoduje, że encodowany jest tylko
	 * parametr "_data"
	 * @param boolean|null $flag
	 * @return boolean
	 */
	public function isNode($flag = null) {
		if($flag == null) {
			return $this->_isNode;
		}
		
		$this->_isNode = (boolean)$flag;
		return $this->_isNode;
	}
	
	/**
	 * Encoduje obiekt do Json'a
	 * @return string
	 */
	public function toJson() {
		return $this->isNode() ? Zend_Json::encode($this->_data) : Zend_Json::encode(array('data'=>$this->_data,'success'=>$this->_success,'error'=>$this->_error));
	}
}