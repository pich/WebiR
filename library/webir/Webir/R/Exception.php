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
 * @version    $Id: Exception.php 10 2010-03-15 15:40:47Z argasek $
 */

/**
 * @author     Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */

class Webir_R_Exception extends Webir_Exception {

	/**
	 * R process exception
	 * 
	 * @param string $message
	 * @param integer $code
	 * @param Exception $previous
	 */
	public function __construct($message = "", $code = 0, Exception $previous = null) {
		$message = sprintf("R process failure: %s", $message);
		parent::__construct($message, $code, $previous);
	}
	
}
