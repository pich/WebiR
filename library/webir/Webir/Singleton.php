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
 * @version    $Id: Singleton.php 108 2010-04-07 09:02:23Z argasek $
 */

/**
 * An abstract class singleton implementation for PHP 5.3.
 *
 * @author Andrea Giammarchi <andrea.giammarchi@gmail.com>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
abstract class Webir_Singleton {
	/**
	 * Singleton instance (actually a child of Webir_Singleton, not Webir_Singleton instance itself).
	 * It has to redefined in a child class
	 *
	 * @var Webir_Singleton
	 */
	protected static $_instance;

	final private function __construct() {
		// If called twice, throw an Exception
		if (static::$_instance !== null) {
			throw new Exception("An instance of " . get_called_class() . " already exists.");
		}

		// Init method via magic static keyword ($this injected)
		static::init();
	}

	/**
	 * No clone allowed, both internally and externally
	 *
	 * @throws Webir_Exception
	 */
	final private function __clone() {
		throw new Webir_Exception("An instance of " . get_called_class() . " is a singleton object and cannot be cloned.");
	}

	/**
	 * The common sense method to retrieve the instance
	 */
	final public static function getInstance() {
		// Ternary operator is that fast!
		return static::$_instance !== null ? static::$_instance : static::$_instance = new static();
	}

	/**
	 * Constructor-like method replacement. It has to be implemented
	 * by each inheriting class on it's own.
	 *
	 */
	abstract protected function init();

}
