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
 * @package    Webir_Auth_SaltShaker_Interface
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Interface.php 21 2010-03-20 15:47:52Z argasek $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
interface Webir_Auth_SaltShaker_Interface {
	/**
	 * Creates salt by input phrase
	 * @param string $phrase
	 * @return string
	 */
	public function salt($phrase);

	/**
	 * Checks if password is correct
	 * @param string $password
	 * @param string $password2Salt
	 * @param string $salt
	 * @return boolean
	 */
	public function isValid($password,$password2Salt,$salt);
	
	/**
	 * Encode plain password to salt one
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	public function encode($password,$salt);
}