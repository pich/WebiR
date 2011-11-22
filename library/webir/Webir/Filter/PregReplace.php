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
 * @package    Webir_Filter
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: PregReplace.php 66 2010-03-29 09:53:14Z argasek $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Filter_PregReplace extends Zend_Filter_PregReplace {
	protected $_limit = null;
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		if ($options instanceof Zend_Config) {
			$options = $options->toArray();
		} else if (!is_array($options)) {
			$options = func_get_args();
			if(array_key_exists(2,$options)) {
				$options['limit'] = $options[2];
			}
		}
		
		if(array_key_exists('limit',$options)) {
			$this->setLimit($options['limit']);
		}
	}
	
	/**
	 * 
	 * @param mixed $limit
	 * @return Webir_Filter_PregReplace
	 */
	public function setLimit($limit) {
		$this->_limit = (int)$limit == 0 ? null : (int)$limit;
		return $this;
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function getlimit() {
		return $this->_limit;
	}
	
	public function filter($value) {
		if ($this->_matchPattern == null) {
    	require_once 'Zend/Filter/Exception.php';
			throw new Zend_Filter_Exception(get_class($this) . ' does not have a valid MatchPattern set.');
		}

		return preg_replace($this->_matchPattern, $this->_replacement, $value,$this->_limit);
	}
}