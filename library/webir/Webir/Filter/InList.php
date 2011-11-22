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
 * @version    $Id: InList.php 66 2010-03-29 09:53:14Z argasek $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Filter_InList implements Zend_Filter_Interface {
	protected $_list = array();
	
	
	public function __construct(array $list = array()) {
		$this->_list = $list;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see library/zend/Zend/Filter/Zend_Filter_Interface#filter($value)
	 */
	public function filter($value) {
		return in_array($value, $this->_list) ? $value : null;
	}
}