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
 * @version    $Id: Json.php 66 2010-03-29 09:53:14Z argasek $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Filter_Json implements Zend_Filter_Interface {
	public function filter($value) {
		return Zend_Json::decode($value);
	}
}