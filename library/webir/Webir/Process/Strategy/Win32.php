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
 * @version    $Id: Win32.php 87 2010-04-01 10:54:12Z argasek $
 */

/**
 * A class for background process handling in Win32 systems.  
 *   
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
final class Webir_Process_Strategy_Win32 extends Webir_Process_Strategy_Abstract {
	
	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Abstract#getExecCommand()
	 */
	protected function getExecCommand() {
		// The -accepteula parameter is mandatory because psexec.exe process is being
		// run from a SYSTEM account and requires accepting the EULA first.
		return $this->getProcessToolsPath('exec') . 'psexec.exe -accepteula';
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Abstract#getListCommand()
	 */
	protected function getListCommand() {
		// The -accepteula parameter is mandatory because psexec.exe process is being
		// run from a SYSTEM account and requires accepting the EULA first.
		return $this->getProcessToolsPath('ps') . 'pslist.exe -accepteula';
	}

	/**
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Abstract#getKillCommand()
	 */
	protected function getKillCommand() {
		// The -accepteula parameter is mandatory because psexec.exe process is being
		// run from a SYSTEM account and requires accepting the EULA first.
		return $this->getProcessToolsPath('kill') . 'pskill.exe -accepteula';
	}

	/**
	 * Execute the command in background (Win32 implementation) 
	 * 
	 * @return integer|false PID of process or false, if running the process failed
	 */
	public function execute($background = true) {
		// Descriptors specification
		$descriptors = array(
			0 => array('pipe', 'r'), // stdin
			1 => array('pipe', 'w'), // stdout
			2 => array('pipe', 'w') // stderr
		);
		
		// Get working directory
		$directory = $this->getWorkingDirectory();
		$directoryArgument = ($directory !== null ? '-w ' . escapeshellarg(trim($directory, '\\')) : '');
		  
		// The process exec command with arguments
		$command = array();
		// The exec command itself
		$command[] = $this->getExecCommand();
		// Run as SYSTEM user
		$command[] = '-s';
		// Specify the working directory
		$command[] = '-d';
		$command[] = $directoryArgument;
		// A process to run
		$command[] = $this->getCommand();
		// Arguments to a process
		$command[] = $this->getArgumentsAsString();
		$command = implode(' ', $command);
		
		// Save the final form of a command to execute (for debugging etc.)
		$this->setFinalExecCommand($command);	
		
		// Options for proc_open() specific for Win32
		$options = array();
		$options['bypass_shell'] = true;
		
		// We try to run the process using psexec.exe binary. 
		$process = proc_open($command, $descriptors, $pipes, $directory, null, $options);
		
		// If proc_open() failed, there was a problem executing psexec command. 
		if (is_resource($process) === false) {
			throw new Webir_Process_Exception(sprintf("Unable to run '%s' command. Please install pstools, make sure they are in PATH or specify the path yourself.", $command));
		}
		
		// Some magic mumbo-jumbo to get contents of standard error output of psexec.exe.
		fclose($pipes[0]);
		fclose($pipes[1]);
		$stderr = '';
		while (!feof($pipes[2])) {
			$stderr .= fgets($pipes[2], 128);
		}
		fclose($pipes[2]);
		proc_close($process);
		
		// Get PID of the process we just fired. 
		if (preg_match("/process ID ([\d]{1,10})\./im", $stderr, $matches)) {
			$pid = (integer) $matches[1];
			//$this->setPid($pid);
			return $pid; 
		}
		
		// If running the process failed, return false.
		return false;
	}

	/**
	 * FIXME: requires functional implementation
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Interface#isAlive($pid)
	 */
	public function isAlive ($pid) {
		 
		$alive=FALSE;
		$dn=dirname(__FILE__);
		$descriptorspec = array(
		0 => array('pipe', 'r'),   // stdin
		1 => array('pipe', 'w'),  // stdout
		2 => array('pipe', 'w')  // stderr
		);
		$fpr = proc_open( 'pslist.exe '.$pid, $descriptorspec, $pipes, $dn );
		fclose($pipes[0]);
		$stdout = '';
		while(!feof($pipes[1])) { $stdout .= fgets($pipes[1], 128); }
		fclose($pipes[1]);
		$stderr = '';
		while(!feof($pipes[2])) { $stderr .= fgets($pipes[2], 128); }
		fclose($pipes[2]);
		proc_close ($fpr);
		if ( strpos($stdout, 'not found') === FALSE )  $alive=TRUE;

		return $alive;
		 
	}
	
	/**
	 * FIXME: requires functional implementation
	 * (non-PHPdoc)
	 * @see webir/Webir/Process/Strategy/Webir_Process_Strategy_Interface#kill($pid)
	 */
	public function kill($pid) {
		$succ=FALSE;
		$dn=dirname(__FILE__);
		$descriptorspec = array(
		0 => array('pipe', 'r'),   // stdin
		1 => array('pipe', 'w'),  // stdout
		2 => array('pipe', 'w')  // stderr
		);
		$fpr = proc_open( 'pskill.exe '.$pid, $descriptorspec, $pipes, $dn );
		fclose($pipes[0]);
		$stdout = '';
		while(!feof($pipes[1])) { $stdout .= fgets($pipes[1], 128); }
		fclose($pipes[1]);
		$stderr = '';
		while(!feof($pipes[2])) { $stderr .= fgets($pipes[2], 128); }
		fclose($pipes[2]);
		proc_close ($fpr);
		if ( strpos($stdout, 'killed') !== FALSE )  $succ=TRUE;
		 
		return $succ;
	}	
	
}