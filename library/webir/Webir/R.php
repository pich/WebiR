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
 * @package    Webir_R
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: R.php 384 2010-04-29 13:26:52Z argasek $
 */

/**
 * A generic R-handling class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_R {
	/**
	 * Output file name
	 *
	 * @var string
	 */
	const OUTPUT_FILENAME = 'output.txt';

	/**
	 * A directory where tasks data processing is going to take place.
	 *
	 * @var string
	 */
	static private $_interpreterPath = '';

	/**
	 * A directory where tasks data processing is going to take place.
	 *
	 * @var string
	 */
	static private $_tasksPath = '';

	/**
	 * The permissions set by default on a task working directory.
	 *
	 * @var string
	 */
	static private $_tasksPathPermissions = 0700;

	/**
	 * Default name of analysis frame (rData file)
	 */
	static private $_frameDefaultName = 'data';

	/**
	 * An injected resource object.
	 *
	 * @var Webir_Application_Resource_R
	 */
	static private $_resource;

	/**
	 * The full path of a concrete R task.
	 *
	 * @var string
	 */
	private $_taskPath;

	/**
	 * PID of R process. False, if process execution failed.
	 *
	 * @var integer|bool
	 */
	private $_pid;

	/**
	 * Output of R process.
	 *
	 * @var string
	 */
	private $_output = '';

	/**
	 * Holds the old (usually: application default) working directory.
	 *
	 * @var string
	 */
	private $_oldWorkingDirectory;

	/**
	 * Arguments to R interpreter.
	 *
	 * @var array
	 */
	private $_arguments;

	/**
	 * Get the tasks path.
	 *
	 * @return string
	 */
	static public function getTasksPath() {
		return self::$_tasksPath;
	}

	/**
	 * Set the tasks path.
	 *
	 * @param string $tasksPath A writable directory
	 */
	static protected function setTasksPath($tasksPath) {
		self::$_tasksPath = $tasksPath;
	}

	/**
	 * Get the tasks working directory permissions.
	 *
	 * @return string
	 */
	static protected function getTasksPathPermissions() {
		return self::$_tasksPathPermissions;
	}

	/**
	 * Set the tasks working directory permissions.
	 *
	 * @param integer $tasksPathPermissions A writable directory
	 */
	static protected function setTasksPathPermissions($tasksPathPermissions) {
		self::$_tasksPathPermissions = $tasksPathPermissions;
	}

	/**
	 * Get data frame default name
	 * @return string
	 */
	static public function getFrameDefaultName() {
		return self::$_frameDefaultName;
	}

	/**
	 * Set data frame default name
	 * @return string
	 */
	static protected function setFrameDefaultName($name) {
		self::$_frameDefaultName = $name;
	}

	/**
	 * Injects the resource and sets options.
	 *
	 * @param string $tasksPath A writable directory
	 */
	static public function injectResource(Webir_Application_Resource_R $resource) {
		// Save the resource for later use
		self::$_resource = $resource;
		$options = $resource->getOptions();

		// Set up the path to R console interpreter
		Webir_R::setInterpreterPath($options['binary']);
		// Set up R tasks base path
		Webir_R::setTasksPath($options['tasks']['path']);
		// Set up the permissions of a task working directory
		Webir_R::setTasksPathPermissions(octdec($options['tasks']['permissions']));
	}

	/**
	 * Get interpreter path.
	 *
	 * @return string
	 */
	static protected function getInterpreterPath() {
		return self::$_interpreterPath;
	}

	/**
	 * Set the R command line interpreter path.
	 *
	 * @param string $interpreterPath
	 */
	static protected function setInterpreterPath($interpreterPath) {
		self::$_interpreterPath = $interpreterPath;
	}

	/**
	 * Run an instance of R process.
	 *
	 * @param array $arguments Command line arguments to R.
	 * @param string $taskDirectory
	 * @throws Webir_R_Exception
	 * @throws Webir_Process_Exception
	 */
	public function __construct(array $arguments = array(), $taskDirectory = '') {
		// Running R interpreter doesn't make any sense in case when there are no arguments provided
		if (count($arguments) === 0) {
			throw new Webir_R_Exception("Not enough arguments provided to R interpreter");
		}
		$this->setArguments($arguments);

		// Task directory has to be specified as string. If not, bail out
		if (is_string($taskDirectory) === false) {
			throw new Webir_R_Exception("Invalid task directory specified");
		}

		// If directory name was not specified, create a random one.
		$taskDirectory = $taskDirectory ?: Webir_Util_UUID::v4();

		// Set the task path
		$this->setTaskDirectory($taskDirectory);

		// Try to create a directory if it doesn't exist
		if (is_dir($this->getTaskPath()) === false) {
			mkdir($this->getTaskPath());
			chmod($this->getTaskPath(), self::getTasksPathPermissions());
		}
		// If directory still doesn't exists at this step, we cannot operate properly
		if (is_dir($this->getTaskPath()) === false) {
			throw new Webir_R_Exception(sprintf("Could not create task directory (%s), or specified directory does not exists", $taskDirectory));
		}
		// If it exists, but is not writable, we are unhappy, too
		if (is_writable($this->getTaskPath()) === false) {
			throw new Webir_R_Exception(sprintf("Task directory (%s) is not writable", $taskDirectory));
		}
	}

	public function kill() {
		$pid = $this->getPid();
		if (is_int($pid)) {
			return Webir_Process::kill($pid);
		};
		return false;
	}

	/**
	 * Runs the task
	 */
	public function run($background = true) {
		// Check if the process is not running already. If so, bail out
		if (is_int($this->getPid())) return;
		// Remember current working directory and change to a new one
		$taskPath = $this->getTaskPath();
		$this->switchWorkingDirectory($taskPath);
		// Try to execute a command
		$pid = Webir_Process::run(
			self::getInterpreterPath(),
			$this->getArguments(),
			$taskPath,
			$background
		);
		// Get the output and try save it to output.txt in the working directory
		// FIXME: there's no place where $this->_output gets set!
		file_put_contents(self::OUTPUT_FILENAME, $this->getOutput(), FILE_APPEND);
		// Change back to the old working directory
		$this->switchWorkingDirectory();
		// Set the PID (or false, if running failed)
		$this->setPid($pid);
	}

	public function delete() {
		$wSettings = Zend_Registry::get('webir');
		if($wSettings['taskDebug'] == 1) {
			return true;
		}
		$di = new DirectoryIterator($this->getTaskPath());
		foreach($di as $file) {
			if($file->isDot()) {continue;}
			@unlink($file->getPathname());
		}

		@rmdir($this->getTaskPath());
	}

	/**
	 * Changes current working directory to $directory and remembers the old working directory.
	 * If $directory is not provided, reverts current working directory to the saved old working directory.
	 *
	 * @param string $directory New working directory
	 * @return string Working directory after switch ($directory or old directory)
	 */
	private function switchWorkingDirectory($directory = null) {
		// If directory was not provided and there's old one saved, switch to it
		if ($directory === null && $this->_oldWorkingDirectory !== null) {
			chdir($this->_oldWorkingDirectory);
		// Remember the current directory and switch to a new one
		} else {
			$this->_oldWorkingDirectory = getcwd();
			// The constructor takes care of $directory existence.
			chdir($directory);
		}
		return getcwd();
	}

	/**
	 * Get the R task process ID.
	 *
	 * @return integer|bool
	 */
	public function getPid() {
		return $this->_pid;
	}

	/**
	 * Get the R task process text output.
	 *
	 * @return string
	 */
	public function getOutput() {
		return $this->_output;
	}


	/**
	 * Set the R task process ID.
	 *
	 * @param integer $pid
	 */
	public function setPid($pid) {
		$this->_pid = $pid;
	}

	/**
	 * Get the full filesystem path of this R task
	 *
	 * @return string
	 */
	public function getTaskPath() {
		return $this->_taskPath;
	}

	/**
	 * Get name of task directory
	 * @return string
	 */
	public function getTaskDirectory() {
		return ltrim(str_replace(self::$_tasksPath,'',$this->_taskPath),'/');
	}

	public function setTaskDirectory($taskDirectory) {
		$this->setTaskPath(self::getTasksPath() . DS . $taskDirectory);
	}

	/**
	 * Set the full filesystem path of this R task
	 *
	 * @param string $taskPath
	 */
	private function setTaskPath($taskPath) {
		$this->_taskPath = $taskPath;
	}

	/**
	 * Get the R task arguments.
	 *
	 * @return integer|bool
	 */
	private function getArguments() {
		return $this->_arguments;
	}

	/**
	 * Set the R task arguments.
	 *
	 * @param array $arguments
	 */
	private function setArguments(array $arguments) {
		// Add the standard arguments
		array_unshift($arguments, '--vanilla');
		array_unshift($arguments, '--quiet');
		$this->_arguments = $arguments;
	}

}