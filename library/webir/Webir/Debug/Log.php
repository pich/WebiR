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
 * @version    $Id: Log.php 7 2010-02-18 11:41:03Z argasek $
 */

/**
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Debug_Log extends Zend_Log {
	/**
	 * Messages below or equal to this prioritylevel are actually logged in.
	 * @var integer
	 */
	protected $_logLevel = Zend_Log::WARN;
	
	/**
	 * Class constructor. Create a new logger
	 *
	 * @param Zend_Log_Writer_Abstract|null  $writer  Default writer
	 * @param integer $logLevel Maximum logging verbosity (default: only emergency errors)
	 */
	public function __construct(Zend_Log_Writer_Abstract $writer = null, $logLevel = Zend_Log::WARN) {
		parent::__construct($writer);
		// The new priority (needed especially for FirePHP)
		$this->addPriority('TABLE', 8);
		// We set the default log level
		$this->setLogLevel($logLevel);
	}

	/**
	 * Log a message at a priority, unless it exceeds logging verbosity.
	 *
	 * @param  string   $message   Message to log
	 * @param  integer  $priority  Priority of message
	 * @param  mixed    $extras    Extra information to log in event
	 * @return Webir_Debug_Log
	 * @throws Zend_Log_Exception
	 */
	public function log($message, $priority, $extras = null) {
		if ($priority <= $this->_logLevel) {
			parent::log($message, $priority, $extras);
		}

		return $this;
	}

	/**
	 * Sets the logging logLevel. The higher level is, the more detailed
	 * information is being logged (EMERG means only critical messages,
	 * DEBUG means a lot of information, @see Zend_Log for details)
	 *
	 * @param integer $level Verbosity level
	 * @return Webir_Debug_Log
	 */
	public function setLogLevel($level) {
		$this->_logLevel = $level;

		return $this;
	}
	
	
	/**
	 * Get the current logging verbosity.
	 * 
	 * @return integer
	 */
	public function getLogLevel() {
		return $this->_logLevel;
	}
	
}