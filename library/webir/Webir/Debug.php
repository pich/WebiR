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
 * @package    Webir_Debug
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Debug.php 66 2010-03-29 09:53:14Z argasek $
 */

/**
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Debug extends Webir_Singleton {
	/**
	 * @var Webir_Debug
	 */
	static protected $_instance;
	
	/**
	 * @var array
	 */
	private $_loggers = array();
	
	/**
	 * @var array
	 */
	protected $_defaultLoggers = array();
	
	/**
	 * A singleton constructor.
	 * @see webir/Webir/Webir_Singleton#init()
	 */
	public function init() {
		
	}
	
	/**
	 * Logs an object/message passed in as $arguments using method $name by all registered loggers.
	 * 
	 * @param string $name Name of a method
	 * @param array $arguments Array of Zend_Log_Writer_Abstract
	 */
	public function __call($name, array $arguments) {		
		$loggers = count($arguments) > 1 ? array_pop($arguments) : $this->getDefaultLoggers();
				
		foreach ($loggers as $logger) {
			// Calls $this->{$logger}::$name($arguments), where $name is a logger method (like 'debug', 'warn' etc.)
			// $this->{$logger} is magically available via self::__get()
			call_user_func_array(array($this->{$logger}, $name), $arguments);
		}
	}
	
	/**
	 * If user reaches a non-existant field of this singleton, we assume
	 * he/she/it means a registered logger of that name.
	 * 
	 * @param $name
	 * @param $arguments
	 * @return unknown_type
	 */
	static public function __callStatic($name, array $arguments) {
		self::getInstance()->__call($name, $arguments);
	}
	
	/**
	 * If user reaches a non-existant field of $this object, we assume
	 * he/she/it means a registered logger of that name.
	 *   
	 * @param string $name
	 * @return Webir_Debug_Log
	 */
	public function __get($name) {
		if ($this->isLoggerRegistered($name) === false) {
			throw new Webir_Debug_Exception(sprintf("Tried to access an unregistered logger ('%s')", $name));	
		}
		
		return $this->getLogger($name);
	}
	
	/**
	 * Returns logger by a given $name. If no such logger exists, return null.
	 *  
	 * @param string $name
	 * @return Webir_Debug_Log|null
	 */
	protected function getLogger($name) {
		return array_key_exists($name, $this->_loggers) ? $this->_loggers[$name] : null; 		
	}
	
	/**
	 * Puts a provided $logger of a given $name in an array of loggers.
	 * 
	 * @param $name Name of the logger
	 * @param Webir_Debug_Log $logger Logger object
	 * @return Webir_Debug $this
	 */
	protected function setLogger($name, Webir_Debug_Log $logger) {
		$this->_loggers[$name] = $logger;
		return $this;
	}
	
	/**
	 * Registers new logger.
	 * 
	 * @param Zend_Log_Writer_Abstract $logger Writer used by a logger.
	 * @param string $name Name of the logger. Must be unique.
	 * @param int $logLevel
	 * @return Webir_Debug $this
	 */
	public function registerLogger(Zend_Log_Writer_Abstract $writer, $name, $logLevel = null) {
		$logger = new Webir_Debug_Log($writer);
		
		if ($logLevel !== null) {
			$logger->setLogLevel($logLevel);
		}
		
		$this->setLogger($name, $logger);

		return $this;
	}
	
	/**
	 * Get the default loggers array.
	 * 
	 * @return array
	 */
	public function getDefaultLoggers() {
		return $this->_defaultLoggers;
	}
	
	/**
	 * Set up which of the registered loggers should serve as the default loggers.
	 * 
	 * @param array $loggers Array of strings (loggers names)
	 * @return Webir_Debug $this
	 */
	public function setDefaultLoggers(array $loggers) {
		foreach ($loggers as $logger) {
			// If such logger exists, everything is OK
			if ($this->isLoggerRegistered($logger) === true) continue;
			// If not, make a boo boo
			throw new Webir_Debug_Exception(
				sprintf("Tried to set up a logger named '%s' as one of the default loggers, but no such logger was registered yet", $logger)
			);	
		}
		// Replace the current set of default loggers with a new one 
		$this->_defaultLoggers = $loggers;
		
		return $this;
	}	
	
	/**
	 * Checks whether logger named $name is registered.
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function isLoggerRegistered($name) {
		return $this->getLogger($name) instanceof Webir_Debug_Log;
	}
	
}