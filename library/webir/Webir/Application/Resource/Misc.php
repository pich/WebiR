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
 * @version    $Id: Misc.php 21 2010-03-20 15:47:52Z argasek $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Application_Resource_Misc extends Zend_Application_Resource_ResourceAbstract {
	public function init() {
		Zend_Registry::set('misc',$this->_options);
	}
}