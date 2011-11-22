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
 * @version    $Id: Manager.php 387 2010-06-07 14:50:11Z argasek $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Process_Manager extends Webir_Singleton {
	/**
	 * @var Webir_Process_Manager
	 */
	static protected $_instance;

	/**
	 * Number of running tasks
	 *
	 * @var integer
	 */
	static protected $_running;
	
	/**
	 *
	 * @var Array
	 */
	private $_options;
	private $_stats = array('new'=>0,'canceled'=>0,'success'=>0);
	protected function init() {
		$this->_options = Zend_Registry::get("webir");
	}

	/**
	 * Manage tasks
	 * @return void
	 */
	public function manage() {
		$tasks = $this->getRunning();
		$this->debug(sprintf("W bazie danych znaleziono %d uruchomionych zadań, przetwarzam...", $tasks->count()));
		foreach ($tasks as $task) {
			$this->_manageTask($task);
		}

		$free_slots = $this->option('slots') - self::$_running;

		if ($free_slots > 0) {
			$dql = Doctrine_Query::create()->from('App_R_Task t')->where('t.status_id = ?',App_R_Task::STATUS_NEW)->orderBy('t.created_at ASC')->limit($free_slots);
			$wSettings = Zend_Registry::get('webir');
			if(APPLICATION_ENV == 'cli-dev' && isset($wSettings['user_id'])) {
				$dql->addWhere('t.user_id = ?',$wSettings['user_id']);
			}
			$tasks = $dql->execute();
			if($tasks->count() > 0) {
				foreach($tasks as $task) {
					$this->_runTask($task);
					$this->_stats['new']++;
				}
			}
		}
		
		return $this->_stats;
	}
	
	public function setLogger($logger) {
		$this->_logger = $logger;
	}
	
	public function debug($text) {
		if (isset($this->_logger)) {
			$this->_logger->debug('INFO: ' . $text);
		}		
	}

	/**
	 * Manage task
	 * @return void
	 */
	protected function _manageTask(App_R_Task $task) {
		// Save information (timestamp) in the DB the status was just checked 
		$task->checkStatus();
		// If process of given pid is not alive, the processing has ended with either success or failure.
		if (Webir_Process::isAlive($task->pid) === false) {
			$this->debug(sprintf("Task (PID: %d) is not running, and the result is...", $task->pid));
			try {
				$result = $task->getFunction()->getResult();
				$task->success();
				if (empty($result->errors)) {
					$task->success();
					$this->debug('...success');
				} else {
					$task->failure();
					$this->debug('...failure');
				}
			} catch(Exception $e) {
				$task->failure();
				$this->debug('...failure (exception occured during getting processing result)');
			}
			self::$_running--;
			$this->_stats['success']++;
		} else {
			$this->debug(sprintf("Task (PID: %d) is running, checking if it exceeds execution time...", $task->pid));
			if ($task->getExecutionTime() > $this->option('max_execution_time')) {
				$this->debug('...yes, cancelling task.');				
				Webir_Process::kill($task->pid);
				$task->cancel();
				self::$_running--;
				$this->_stats['canceled']++;
			} else {
				$this->debug('...no, leave it alone.');
			}
		}
	}

	/**
	 * Run task
	 * @return void
	 */
	protected function _runTask(App_R_Task $task) {
		$task->start();
		self::$_running++;
	}

	/**
	 * Get running processes
	 * @return Doctrine_Collection
	 */
	public function getRunning() {
		$dql = Doctrine_Query::create()->from('App_R_Task t')->leftJoin('t.user u')->where('t.status_id = ?',App_R_Task::STATUS_IN_PROGRESS);
		$wSettings = Zend_Registry::get('webir');
		if(APPLICATION_ENV == 'cli-dev' && isset($wSettings['user_id'])) {
			$dql->addWhere('t.user_id = ?',$wSettings['user_id']);
		}
		$tasks = $dql->execute();
	
		self::$_running = $tasks->count();
		return $tasks;
	}

	/**
	 * Get or set option
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 * @throws Webir_Exception
	 */
	public function option($name,$value = null) {
		if($value != null) {
			$this->_options[$name] = $value;
		}

		if($this->hasOption($name)) {
			return $this->_options[$name];
		} else {
			throw new Webir_Exception('Option doesn\'t exist');
		}
	}

	/**
	 * Checks if option exist
	 * @param string $key
	 * @return boolean
	 */
	public function hasOption($key) {
		return isset($this->_options[$key]);
	}
}