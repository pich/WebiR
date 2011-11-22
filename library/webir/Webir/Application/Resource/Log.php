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
 * @version    $Id: Log.php 8 2010-03-05 16:00:26Z argasek $
 */

/**
 * Loggers resource
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Application_Resource_Log extends Zend_Application_Resource_ResourceAbstract {
	/**
	 * Log instance.
	 * @var Webir_Debug
	 */	
	protected $_log;
	
	public function init() {
		// Return Webir_Debug resource so bootstrap will store it in the registry
		return $this->getLog();
	}

	public function getLog() {
		if ($this->_log === null) {
			$this->_log = Webir_Debug::getInstance();
			foreach ($this->_options['loggers'] as $key => $logger) {
				$writerReflection = new ReflectionClass($logger['writer']);
				// Constructor arguments
				$args = array_key_exists('args', $logger) ? $logger['args'] : array();
				// log level
				$logLevel = array_key_exists('logLevel', $logger) ? (int) $logger['logLevel'] : null;
					
				$this->_log->registerLogger($writerReflection->newInstanceArgs($args), $key, $logLevel);
			}
	
			$this->_log->setDefaultLoggers($this->_options['defaultLoggers']);
		}
		return $this->_log;
	}
}
