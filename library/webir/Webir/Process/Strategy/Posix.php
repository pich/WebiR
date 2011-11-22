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
 * @version    $Id: Posix.php 384 2010-04-29 13:26:52Z argasek $
 */

/**
 * A class for background process handling in POSIX compliant systems.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */

final class Webir_Process_Strategy_Posix extends Webir_Process_Strategy_Abstract {
	/**
	 * @var array
	 */
	private $_processToolsPath = array(
		// The execute command path
		'exec' => '/usr/bin/',
		// The kill command path
		'kill' => '/bin/',
		// The ps command path
		'ps' => '/bin/'
	);

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Abstract#getExecCommand()
	 */
	protected function getExecCommand() {
		// FIXME: apparently it doesn't work for some reason as expected :-(
		// return $this->getProcessToolsPath('exec') . 'nohup';
		return '';
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Abstract#getListCommand()
	 */
	protected function getListCommand() {
		// The -accepteula parameter is mandatory because psexec.exe process is being
		// run from a SYSTEM account and requires accepting the EULA first.
		return $this->getProcessToolsPath('ps') . 'ps';
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Abstract#getKillCommand()
	 */
	protected function getKillCommand() {
		// The -accepteula parameter is mandatory because psexec.exe process is being
		// run from a SYSTEM account and requires accepting the EULA first.
		return $this->getProcessToolsPath('kill') . 'kill';
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Interface#execute()
	 */
	public function execute($background = true) {
		// The process exec command with arguments
		$command = array();
		// The exec command itself
		$command[] = $this->getExecCommand();
		// A process to run
		$command[] = $this->getCommand();
		// Arguments to a process
		$command[] = $this->getArgumentsAsString();
		// Additional shell arguments
		if ($background === true) {
			$command[] = '> /dev/null';
			$command[] = '&';
			$command[] = 'echo $!';
		}

		$command = trim(implode(' ', $command));

		// Save the final form of a command to execute (for debugging etc.)
		$this->setFinalExecCommand($command);

		// Check if executable file exists. If so, run a command.
		// TODO: a more reliable mechanism is required, i.e. getting status
		// if running command succeeded directly from the OS.
		$pid = false;
		$output = array();
		Webir_Debug::debug('isValidCommand(): ' . ($this->_isValidCommand() ? 'true' : 'false'));
		Webir_Debug::debug('Command: ' . $command);
		if ($this->_isValidCommand()) {
			$pid = (integer) trim(exec($command, $output));
			$this->_output = implode("\n", $output);
			Webir_Debug::debug('PID: ' . $pid);
			Webir_Debug::debug('Output: ' . $this->_output);
		}

		return $pid;
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Interface#kill($pid)
	 */
	public function kill($pid) {
		// The process kill command
		$command = array();
		// The exec command itself
		$command[] = $this->getKillCommand();
		// A signal
		$command[] = (string) '-9';
		// A process to kill
		$command[] = (string) $pid;

		$command = trim(implode(' ', $command));
		$pid = (integer) trim(exec($command, $output));
		$this->_output = implode("\n", $output);

		if ($this->isAlive($pid) === true) {
			return false;
		}
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Interface#isAlive($pid)
	 */
	public function isAlive($pid) {
		$command = array();
		$command[] = $this->getListCommand();
		$command[] = $pid;
		$command = trim(implode(' ', $command));
		$output = array();
		exec($command, $output);
//		Webir_Debug::debug($output);
		if (count($output) >= 2) {
			return true;
		}
		return false;
	}


	/**
	 * Searches PATH environmental variable, a working or a current directory in search of
	 * a command.
	 *
	 * @return bool
	 */
	private function _isValidCommand() {
		$command = $this->getCommand();

		// First, we need to check if command is not by chance a combination of path + command.
		// If so, we just try if file exists and is executable.
		if (is_executable($command)) {
			return true;
		}

		$workingDirectory = $this->getWorkingDirectory();

		// These are the potential locations of command, i.e. working directory (or
		// current directory, if empty) plus PATH directories
		$paths = array();
		$paths[] = ($workingDirectory === null ? '.' : trim($workingDirectory, DIRECTORY_SEPARATOR));
		$paths = explode(PATH_SEPARATOR, getenv('PATH'));

		// We check within all available paths. If found, stop. We are happy now
		foreach ($paths as $path) {
			$file = $path . DIRECTORY_SEPARATOR . $command;
			if (is_executable($file)) {
				return true;
			}
		}

		// We couldn't find the executable.
		return false;
	}

}