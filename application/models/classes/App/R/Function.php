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
 * @package    App_R
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Function.php 323 2010-04-19 07:49:54Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function extends Webir_R_Function_Abstract {
	static function factory($type,Array $options=array()) {
		$type = ucfirst($type);
		
		$class = 'App_R_Function_'.$type;
		if(!class_exists($class,true)) {
			throw new Webir_Exception('Nie znaleziono klasy: '.$class);
		}
		
		return new $class($options);
	}
}