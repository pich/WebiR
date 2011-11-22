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
 * @category   App
 * @package    App_R
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Task.php 387 2010-06-07 14:50:11Z argasek $
 */

/**
 *
 * @property integer $id Numerical ID of the task.
 * @property integer $pid Process ID (PID) of the task.
 * @property integer $status_id Task's status.
 * @property string $name Task human readable name
 * @property string $created_at UTC date and time where the task was created
 * @property string $started_at UTC date and time where the task was started
 * @property string $checked_at UTC date and time where the task was last checked for being still run
 * @property string $status_at UTC date and time where the task status was changed
 * @property string $seen_at UTC date and time where the user has seen his/her task. Null if none
 * @property string $directory Task's directory relative to tasks directory
 * @property integer $user_id ID of user who queued the task
 * @property integer $data_set_id ID of dataset the task is operating on
 * @property App_R_DataSet $data_set
 * @property App_User $user
 * @property Doctrine_Collection $variables
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Task extends Doctrine_Record {
	/**
	 * New task
	 */
	const STATUS_NEW = 1;

	/**
	 * Task in progress
	 */
	const STATUS_IN_PROGRESS = 2;

	/**
	 * Task end sucessfully
	 */
	const STATUS_SUCCESS = 3;

	/**
	 * Task canceled
	 */
	const STATUS_CANCELED = 4;

	/**
	 * Task failured
	 */
	const STATUS_FAILURED = 5;

	/**
	 * Task in basic mode
	 */
	const MODE_BASIC = 'basic';

	/**
	 * Task in advance mode
	 */
	const MODE_ADVANCE = 'advance';

	/**
	 * One variable: factor
	 */
	const TYPE_FACTOR = 'factor';

	/**
	 * One variable: nonfactor
	 */
	const TYPE_NONFACTOR = 'nonfactor';


	protected $_task;
	protected $_function;

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Record/Doctrine_Record_Abstract#setTableDefinition()
	 */
	public function setTableDefinition() {
		$this->setTableName('r_task');

		$this->hasColumn('id','integer',4,array('primary'=>true,'autoincrement'=>true));
		$this->hasColumn('pid','integer',4,array('notnull'=>false));
		$this->hasColumn('status_id','integer',1,array('notnull'=>true));
		$this->hasColumn('name','string',128,array('notnull'=>true));
		$this->hasColumn('started_at','timestamp',null,array('notnull'=>false));
		$this->hasColumn('checked_at','timestamp',null,array('notnull'=>false));
		$this->hasColumn('status_at','timestamp',null,array('notnull'=>false));
		$this->hasColumn('seen_at', 'timestamp',null,array('notnull'=>false));
		$this->hasColumn('user_id','integer',4,array('notnull'=>true));
		$this->hasColumn('directory','string',null,array('notnull'=>false));
		$this->hasColumn('hash','string',256,array('notnull'=>true));
		$this->hasColumn('function','string',256,array('notnull'=>true));
		$this->hasColumn('data_set_id','integer',4,array('notnull'=>false));

		$this->actAs('Timestampable',array('created'=>array('type'=>'timestamp','expression'=>'atTimeZone(UTC)')
		,'updated'=>array('disabled'=>true)));

		$this->hasAccessor('directory','getDirectory');
	}

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Doctrine_Record#setUp()
	 */
	public function setUp() {
		$this->hasOne('App_User as user',array('local'=>'user_id','foreign'=>'id','onDelete'=>'CASCADE','onUpdate'=>'CASCADE'));
		$this->index('fki_r_task_user_id_fkey', array('fields' => array('user_id')));
		$this->hasOne('App_R_DataSet as data_set',array('local'=>'data_set_id','foreign'=>'id','onDelete'=>'CASCADE','onUpdate'=>'CASCADE'));
		$this->index('fki_r_task_data_set_id_fkey', array('fields' => array('data_set_id')));
	}

	public function getDirectory() {
		if(!is_string($this->_data['directory']) || empty($this->_data['directory'])) {
			$this->directory = Webir_Util_UUID::v4();
		}

		return $this->_data['directory'];
	}

	/**
	 * @return Webir_R
	 */
	public function getTask() {
		if (!isset($this->_task)) {
			$this->_task = new Webir_R(array('-f', escapeshellcmd('script.R')), (string)$this->directory);
			$this->_task->setPid($this->pid);
		}

		return $this->_task;
	}

	/**
	 *
	 * @return App_R_Function
	 */
	public function getFunction() {
		if(!isset($this->_function)) {
			$this->_function = App_R_Function::factory($this->function,array('task'=>$this->getTask()));
		}

		return $this->_function;
	}

	public function getResult() {
		return $this->getFunction()->getResult();
	}

	/**
	 * Runs current task as R process
	 * @return void
	 */
	public function start() {
		$r = $this->getTask();
		$r->run();
		$pid = $r->getPid();
		if ($pid === false) {
			throw new Webir_Exception('(Re)spawning of process failed - could not run task');
		}
		$this->pid = $pid;
		$this->started_at = new Doctrine_Expression('atTimeZone(UTC)');
		$this->status(self::STATUS_IN_PROGRESS, false);
		$this->save();
	}

	/**
	 * Change current task's status to CANCELED
	 * @return void
	 */
	public function cancel() {
		$r = $this->getTask();
		$result = ($r->getPid() !== null ? $r->kill() : true);
		if ($result === true) {
			$this->pid = null;
			$this->status(self::STATUS_CANCELED);
		} else {
			throw new Webir_R_Exception(sprintf("Nie udało się anulować procesu o PID: '%s' ", $r->getPid()));
		}
	}

	/**
	 * Change current task's status to SUCCESS
	 * @return void
	 */
	public function success() {
		$this->pid = null;
		$this->status(self::STATUS_SUCCESS);
	}

	/**
	 * Change current task's status to SUCCESS
	 * @return void
	 */
	public function failure() {
		$this->pid = null;
		$this->status(self::STATUS_FAILURED);
	}

	/**
	 * Check or set status
	 * @param string $status
	 * @return string current status
	 */
	public function status($status = null,$save = true) {
		if(empty($status)) {
			return $status;
		}

		$this->status_id = $status;
		$this->status_at = new Doctrine_Expression('atTimeZone(UTC)');
		if($save == true) {
			$this->save();
		}

		return $status;
	}

	public function checkStatus() {
		$this->status(self::STATUS_IN_PROGRESS,false);
		$this->checked_at = new Doctrine_Expression('atTimeZone(UTC)');
		$this->save();
	}

	/**
	 * Get task execution time
	 * @return integer
	 */
	public function getExecutionTime() {
		$checked_at = new Zend_Date($this->checked_at,'yyyy-MM-dd HH:mm:ss');
		$started_at = new Zend_Date($this->started_at,'yyyy-MM-dd HH:mm:ss');
		return $checked_at->sub($started_at)->toValue();
	}

	/**
	 * Get number of tasks not yet seen by user.
	 *
	 * @param integer $uid User ID
	 * @return integer
	 */
	public static function getNumberOfUnseenTasks($uid) {
		$dql = Doctrine_Query::create()->select('id')->from(__CLASS__)->where('user_id = ?', (int) $uid)->addWhere('seen_at IS NULL')->addWhere('status_id = ?', self::STATUS_SUCCESS);
		return $dql->count();
	}

	/**
	 * Mark the task was seen (displayed) by a user.
	 */
	public function updateSeen() {
		$this->seen_at = new Doctrine_Expression('atTimeZone(UTC)');
		$this->save();
	}

	/**
	 * Returns string representation of current task type.
	 * Type is composed of analyzed variables' type, seperate by underscore ("_").
	 * If one variable has more than one type, the camelCase notation is used.
	 * Sometimes different types are replaced by one (e.g. numeric or integer => quantitative)
	 * Examples of return types:
	 * One integere variable => quantitative
	 * Two variables: first one "numeric", other one "factor" => quantitative_factor
	 * Two variables: first one "ordered factor", other one "integer" => orderedFactor_quantitative
	 *
	 * @return string|false
	 */
	public function getResultType() {
		$f = new Zend_Filter_Word_SeparatorToCamelCase(' ');
		if($this->variables->count() == 1) {
			// jeśli zmienna jest typu factor (lub ordered factor) -> factor
			return in_array('factor',explode(' ',$this->variables->get(0)->type)) ? 'factor' : 'nonfactor';
		}

		if($this->variables->count() > 1) {
			foreach($this->variables as $variable) {
				$type = in_array($variable->type,array('integer','numeric')) ? 'quantitative' : $variable->type;
				$tmp[] = lcfirst($f->filter($type));
			}
			return isset($tmp) ? implode('_',$tmp) : false;
		}
	}

	/**
	 * This method returns Array with results of task. Each result type (@see getResultType()) can returns other data, so this method
	 * tries to invoke proper method. The key "output" is added in all cases: this is raw output of R process.
	 * Invoked method must returns array
	 * @return Array
	 */
	public function getResultData() {
		$arResult = array();
		$output = Webir_R::getTasksPath() . DS . $this->directory . DS . 'output.txt';
		$arResult['output'] = file_exists($output) ? nl2br(file_get_contents($output),true) : false;
		$method = '_getResultData'.ucfirst($this->getResultType());
		$arData = method_exists($this,$method) ? call_user_func(array($this,$method)) : false;
		return $arData ? array_merge($arResult,$arData) : $arResult;
	}

	protected function _getResultDataQuantitative_Factor() {
		$arFiles = array('anova.csv','tstudent.csv','kraskal.csv','wilcoxon.csv');
		$arResult = array();

		// błąd zmiennych wejściowych
		if(file_exists(Webir_R::getTasksPath() . DS . $this->directory . DS . 'error.csv')) {
			$arResult['errors'][] = file_get_contents(Webir_R::getTasksPath() . DS . $this->directory . DS . 'error.csv');
			return $arResult;
		}

		// sprawdzamy jaki test został wykonany
		$arResult['test'] = null;
		foreach($arFiles as $file) {
			if(file_exists(Webir_R::getTasksPath() . DS . $this->directory . DS . $file)) {
				$arResult['test'] = mb_substr($file,0,-4);
				$f = fopen(Webir_R::getTasksPath() . DS . $this->directory . DS . $file,'r');
				$arResult['test_result'] = array();
				while($row = fgetcsv($f,0,';','"')) {
					if(empty($row[0])) {continue;}
					$arResult['test_result'][] = array($row[0], preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[1]));
				}
				fclose($f);
				break;
			}
		}

		if(empty($arResult['test'])) {
			$arResult['errors'][] = 'Nie znaleziono żadnego z plików wynikowych: '.implode(', ',$arFiles);
			return $arResult;
		}

		if(!file_exists(Webir_R::getTasksPath() . DS . $this->directory . DS . 'srednie.csv')) {
			$arResult['errors'][] = 'Nie znaleziono pliku wynikowego: średnie.csv';
			return $arResult;
		}

		$arResult['srednie'] = array();
		$f = fopen(Webir_R::getTasksPath() . DS . $this->directory . DS . 'srednie.csv','r');
		while($row = fgetcsv($f,0,';','"')) {
			if(empty($row[0])) {continue;}
			$arResult['srednie'][] = array($row[0],preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[1]));
		}

		if($arResult['test'] == 'anova') {
			if(!file_exists(Webir_R::getTasksPath() . DS . $this->directory . DS . 'compare.csv')) {
				$arResult['errors'][] = 'Brak pliku wynikowego: compare.csv';
			} else {
				$f = fopen(Webir_R::getTasksPath() . DS . $this->directory . DS . 'compare.csv','r');
				$arResult['compare'] = array();
				while($row = fgetcsv($f,0,';','"')) {
					if(empty($row[0])) {continue;}
					$name = array_shift($row);
					$tmpRow = array($name);
					foreach($row as $val) {
						$tmpRow[] = preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$val);
					}
					$arResult['compare'][] = $tmpRow;
				}

				if(file_exists(Webir_R::getTasksPath() . DS . $this->directory . DS . 's_eff.csv')) {
					$f = fopen(Webir_R::getTasksPath() . DS . $this->directory . DS . 's_eff.csv','r');
					$arResult['s_eff'] = array();
					while($row = fgetcsv($f,0,';','"')) {
						if(empty($row[0])) {continue;}
						$arResult['s_eff'] = preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[1]);
					}
				}
			}
		}

		return $arResult;
	}

	public function preInsert($event) {
		if(empty($this->name)) {
			$this->name = $this->directory;
		}

		$this->hash = $this->getFunction()->createScript();
		$this->status(self::STATUS_NEW,false);
	}

	public function delete(Doctrine_Connection $conn = null) {
		$dir = Webir_R::getTasksPath() . DS . $this->directory;

		// Remove entry from the database
		$result = parent::delete($conn);

		// Directory probably doesn't exist for some reason
		if (is_dir($dir) === false) {
			return;
		}

		// Recursively delete directory and its contents
		$di = new DirectoryIterator($dir);
		foreach ($di as $file) {
			if ($file->isDot()) { continue; }
			@unlink($file->getPathname());
		}

		@rmdir($dir);

		return $result;
	}
}