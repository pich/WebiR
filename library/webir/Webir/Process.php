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
 * @package    Webir_Process
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Process.php 384 2010-04-29 13:26:52Z argasek $
 */

/**
 * An abstract class for background process handling.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Process extends Webir_Singleton {
	/**
	 * Strategy of process handling dependent on OS.
	 *
	 * @var Webir_Process_Strategy_Abstract
	 */
	static private $_strategy;

	protected function init() {
		// Let's choose a correct process handling strategy depending on
		// platform PHP is being run on.
		switch (PHP_OS) {
			case 'WIN32':
			case 'WINNT':
			case 'Windows':
				self::$_strategy = new Webir_Process_Strategy_Win32();
				break;
			case 'Darwin':
			case 'Linux':
				self::$_strategy = new Webir_Process_Strategy_Posix();
				break;
			default:
				throw new Webir_Exception(sprintf('No process handling strategy defined for current (%s) operating system.', PHP_OS));
				break;
		}
	}


	/**
	 * Set the path where process execution, listing and killing tools reside.
	 * If not set, the strategy assumes process tools are available via PATH.
	 *
	 * @param string $processToolsPath
	 */
	static protected function setProcessToolsPath($processToolsPath, $name = null) {
		self::$_strategy->setProcessToolsPath($processToolsPath, $name);
	}

	/**
	 * Run a new process in a background.
	 *
	 * @param string $command A command (program) to run.
	 * @param array $arguments An array of command's arguments. May be empty.
	 * @param string $directory A working directory of command.
	 * @param boolean $background Should this process be run in background? Default: yes
	 * @return integer|bool Numeric ID of process (PID) or false, if process startup failed.
	 */
	static protected function run($command, array $arguments = array(), $directory = null, $background = true) {
		// Set the command, arguments and working directory
		self::$_strategy->setCommand($command);
		self::$_strategy->setArguments($arguments);
		self::$_strategy->setWorkingDirectory($directory);

		// Execute the command.
		$pid = self::$_strategy->execute($background);

		// Get the command output
		$output = self::$_strategy->getOutput();

		// Log the command to common.log
		//		Webir_Debug::getInstance()->error->debug(Webir_Util_Text::varDump(array(
		//			'command' => self::$_strategy->getFinalExecCommand(),
		//			'directory' => $directory
		//		)));

		// TODO: it also outputs to syslog when in cli, and syslog doesn't understand arrays
		// Log the command to FireBug
		// Webir_Debug::debug(array(
		//	'command' => self::$_strategy->getFinalExecCommand(),
		//	'directory' => $directory
		//));

		return $pid;
	}

	static protected function isAlive($pid) {
		return self::$_strategy->isAlive($pid);
	}

	static protected function kill($pid) {
		return self::$_strategy->kill($pid);
	}

	/**
	 * A proxy method for calling other methods in class.
	 * __callStatic makes sure a singleton instance exists.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	static public function __callStatic($method, $args) {
		self::getInstance();

		return call_user_func_array(__CLASS__ . '::' . $method, $args);
	}

}
