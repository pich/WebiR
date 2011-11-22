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
 * @version    $Id: Abstract.php 87 2010-04-01 10:54:12Z argasek $
 */

/**
 * An abstract, OS-independent class for process handling.
 *   
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * 
 */
abstract class Webir_Process_Strategy_Abstract implements Webir_Process_Strategy_Interface {
	/**
	 * The directories where process handling tools reside. The keys
	 * in the array below are the standard set of process tools. Each
	 * strategy may redefine this set 
	 * 
	 * @var array[int]string
	 */
	private $_processToolsPath = array(
		// The execute command path
		'exec' => '',
		// The kill command path
		'kill' => '',
		// The ps command path
		'ps' => ''
	);
	
	/**
	 * A process working directory, or null, if we wish to execute a process
	 * in the working directory of PHP.
	 *   
	 * @var string|null
	 */
	private $_workingDirectory;
	
	/**
	 * A command to run (without arguments)
	 *   
	 * @var string
	 */
	private $_command;
	
	/**
	 * A command output
	 *   
	 * @var string
	 */
	protected $_output;
	
	/**
	 * A final form of command to execute, passed to an operating system.
	 * 
	 * @var string
	 */
	private $_finalExecCommand;
	
	/**
	 * Command's arguments
	 *   
	 * @var string
	 */
	private $_arguments = array();
	
	/**
	 * Get the command allowing new process execution.
	 *  
	 * @return string
	 */
	abstract protected function getExecCommand();
	
	/**
	 * Get the process listing command.
	 *  
	 * @return string
	 */
	abstract protected function getListCommand();
	
	/**
	 * Get the command allowing for killing of process.
	 *  
	 * @return string
	 */
	abstract protected function getKillCommand();
	
	/**
	 * Set the path for a given ($name) process tool. If $name is null (default),
	 * sets the common path for all process tools. One should set these paths
	 * when process tools path(s) isn't/aren't in PATH environmental variable.
	 * 
	 * @param string $processToolsPath The pstools path.
	 * @param string|null $name Name of concrete tool (optional) 
	 */
	public function setProcessToolsPath($processToolsPath, $name = null) {
		/**
		 * Tools list
		 * @var array
		 */
		$tools = ($name === null ? array_keys($this->_processToolsPath) : array($name));
		foreach ($tools as $tool) {
			$this->_processToolsPath[$tool] = $processToolsPath;
		}
	}
	
	/**
	 * Gets the path of tool named $name (ex. 'exec', 'kill', 'ps')
	 * 
	 * @param string $name Name of tool
	 * @return string The path of tool
	 */
	protected function getProcessToolsPath($name) {
		if (array_key_exists($name, $this->_processToolsPath) === false) {
			throw new Webir_Process_Exception(
				sprintf(
					"Invalid process tool name requested: '%s'. You should use one of these: %s.",
					$name,
					implode(', ', array_keys($this->_processToolsPath))
				)
			);
		}
		return $this->_processToolsPath[$name];
	}
	
	/**
	 * Get the process working directory. May return null,
	 * which means the working directory is not set. 
	 * 
	 * @return string|null
	 */
	protected function getWorkingDirectory() {
		return $this->_workingDirectory;
	}
	
	/**
	 * Set the process working directory.
	 * 
	 * @param string $workingDirectory
	 */
	public function setWorkingDirectory($workingDirectory) {
		$this->_workingDirectory = $workingDirectory;
	}

	/**
	 * Sets the command to execute in a background.
	 *  
	 * @param string $command
	 */
	public function setCommand($command) {
		if (empty($command)) {
			throw new Exception('A command to execute cannot be empty.');	
		}
		$this->_command = $command;
	}

	/**
	 * Returns the command to execute in a background. 
	 * 
	 * @return string
	 */
	protected function getCommand() {
		return $this->_command;
	}

	/**
	 * Returns the command output (if possible). 
	 * 
	 * @return string
	 */
	public function getOutput() {
		return $this->_output;
	}
	
	
	/**
	 * Returns the complete command passed to system for execution. 
	 * 
	 * @return string
	 */
	public function getFinalExecCommand() {
		return $this->_finalExecCommand;
	}
	
	/**
	 * Sets the complete command passet to system for execution.
	 * 
	 * @param string $command The final command form
	 */
	protected function setFinalExecCommand($command) {
		$this->_finalExecCommand = $command;
	}
	
	/**
	 * Sets the command arguments.
	 *  
	 * @param string $command
	 */
	public function setArguments(array $arguments) {
		$this->_arguments = $arguments;
	}

	/**
	 * Gets the command arguments. 
	 * 
	 * @return array
	 */
	protected function getArguments() {
		return $this->_arguments;
	}
	
	/**
	 * Gets the command arguments escaped for running in a shell. 
	 * 
	 * @return array
	 */
	protected function getEscapedArguments() {
		$arguments = array();
		foreach ($this->_arguments as $argument) {
			$arguments[] = escapeshellarg($argument);
		}
		return $arguments;
	}
	
	/**
	 * Gets the command arguments escaped for running in a shell as a string. 
	 * 
	 * @return string
	 */
	protected function getEscapedArgumentsAsString() {
		return implode(' ', $this->getEscapedArguments());
	}

	/**
	 * Gets the command arguments as a string. 
	 * 
	 * @return string
	 */
	protected function getArgumentsAsString() {
		return implode(' ', $this->getArguments());
	}	
	
}
