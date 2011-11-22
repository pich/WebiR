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
 * @version    $Id: DataSet.php 324 2010-04-19 08:28:47Z dbojdo $
 */

/**
 *
 * @property integer $id
 * @property integer $type_id
 * @property integer $user_id
 * @property string $name
 * @property string $filename
 * @property string $source_filename
 * @property boolean $is_default
 * @property integer $status_id
 * @property string $deleted_at
 * @property string $created_at Dataset creation date
 * @property App_User $user
 * @property stdClass $reader_params
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class App_R_DataSet extends Doctrine_Record {
	/**
	 * System type of DataSet
	 */
	const TYPE_SYSTEM = 1;

	/**
	 * User type of DataSet
	 */
	const TYPE_USER = 2;

	/**
	 * Csv format
	 */
	const FORMAT_CSV = 'csv';

	/**
	 * RData format
	 */
	const FORMAT_RDATA = 'rdata';

	/**
	 * Data set uploaded to server
	 */
	const STATUS_WAITING = 1;

	/**
	 * Data set during analysing by R
	 */
	const STATUS_ANALYSING = 2;

	/**
	 * Data set ready to use
	 */
	const STATUS_READY = 3;

	/**
	 * Data set after processing but unuseable
	 */
	const STATUS_DIRTY = 4;

	public function setTableDefinition() {
		$this->setTableName('r_data_set');

		$this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('name', 'string', 128, array('notnull' => true));
		$this->hasColumn('type_id', 'integer', 1, array('notnull' => true));
		$this->hasColumn('filename', 'string', 256, array('notnull' => true));
		$this->hasColumn('source_filename', 'string', 256, array('notnull' => true));
		$this->hasColumn('user_id', 'integer', 4, array('notnull' => false));
		$this->hasColumn('format','string',8,array('notnull'=>true,'default'=>self::FORMAT_CSV));
		$this->hasColumn('is_default', 'boolean', null, array('notnull' => true, 'default' => 'false'));
		$this->hasColumn('status_id','integer',1,array('notnull'=>true,'default'=>self::STATUS_WAITING));
		$this->hasColumn('description','clob',null,array('notnull'=>true,'default'=>'Brak informacji na temat danych'));
		$this->hasColumn('reader_params','string',null,array('notnull'=>false));
		
		$this->hasAccessor('reader_params','getReaderParams');
		$this->hasAccessor('filename','getFilename');
		
		$this->actAs('SoftDelete');
		$this->actAs('Timestampable', array(
			'created' => array('expression' => 'atTimeZone(UTC)'),
			'updated' => array('disabled' => true),
		));

		$this->setSubclasses(array('App_R_DataSet_System'=>array('type_id'=>self::TYPE_SYSTEM),'App_R_DataSet_User'=>array('type_id'=>self::TYPE_USER)));
	}

	public function setUp() {
		$this->hasOne('App_User as user',array('local'=>'user_id','foreign'=>'id','onDelete'=>'CASCADE','onUpdate'=>'CASCADE'));
		$this->index('fki_r_data_set_user_id_fkey', array('fields' => array('user_id')));
		$this->hasMany('App_R_DataSet_Column as columns',array('local'=>'id','foreign'=>'data_set_id'));
		$this->hasMany('App_R_DataSet_Segment as segments',array('local'=>'id','foreign'=>'data_set_id'));
	}

	public function getReaderParams() {
		if(empty($this->_data['reader_params']) || is_string($this->_data['reader_params'])) {
			$this->_data['reader_params'] = empty($this->_data['reader_params']) ? new stdClass : unserialize($this->_data['reader_params']);
		}
		
		return $this->_data['reader_params'];
	}
	
	public function getFilename() {
		if(!$this->exists() && ($this->_data['filename'] instanceof Doctrine_Null || empty($this->_data['filename']))) {
			$this->filename = Webir_Util_UUID::v4();
		}
		
		return $this->_data['filename'];
	}
	
	/**
	 * Checks if this Dataset is System one
	 * @return boolean
	 */
	public function isSystem() {
		return $this->type_id == self::TYPE_SYSTEM;
	}

	/**
	 * Checks if this Dataset is Default one
	 * @return boolean
	 */
	public function isDefault() {
		return $this->is_default;
	}

	/**
	 * Checks if this Dataset is User one
	 * @return boolean
	 */
	public function isUser() {
		return $this->type_id == self::TYPE_USER;
	}
	
	/**
	 * Sets current dataset as default one
	 * @return void
	 */
	public function setDefault() {
		if ($this->isUser() === true) {
			throw new Webir_Exception('Zbiór danych użytkownika nie może być zbiorem domyślnym.');
		}

		$connection = Doctrine_Manager::getInstance()->getCurrentConnection();

		$connection->beginTransaction();
		Doctrine_Query::create()->update('App_R_DataSet')->set('is_default', '?', 'false')->execute();
		$this->is_default = true;
		$this->save();
		$connection->commit();
	}
	
	/**
	 * Get the default dataset record.
	 *
	 * @return App_R_DataSet|false Returns App_R_DataSet or false if there's no default dataset in a database
	 */
	static public function getDefault() {
		return Doctrine::getTable('App_R_DataSet')->findOneByis_default(true);
	}

	public function preSave($event) {
		$this->reader_params = serialize($this->reader_params);
	}
	
	public function preInsert($event) {
		if (empty($this->name)) {
			
			$this->name = $this->filename;
		}
	}

	/**
	 * Analyze data set by R
	 * @return void
	 */
	public function process() {
		$this->status(self::STATUS_ANALYSING);
		$wSettings = Zend_Registry::get('webir');
		$source = $wSettings['datasetsPath'] . DS . $this->filename;
		$logFile = $source . '.log';
		$file = touch($logFile);

		$log = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$log->info('Zaczynam przetwarzanie...');
		
		// 1. przetwarzanie na rData oraz wyciągnie do CSV typów zmiennych i poziomów factor (R)
		$oFun = App_R_Function::factory('processDataset',array('data_set'=>$this,'params'=>array('reader'=>$this->reader_params)));
		$log->info('Uruchamiam proces R: '.$oFun->getTask()->getTaskPath());
		$oFun->runTask();
		$log->debug('Proces R zakończył działanie');
		$result = $oFun->getResult();

		if(count($result->errors) > 0) {
			foreach($result->errors as $error) {
				$log->err($error);
			}
			$log->info('Zakończyłem działanie z powodu powyższych błędów');
			return $this->status(self::STATUS_DIRTY);
		}
		
		// 2. przeniesienie pliku z katalotu taska do katalogu zbiorów danych
		$log->info('Zapisuję przetworzony plik...');
		rename($oFun->getTask()->getTaskPath() . DS . $this->filename,$source);
		
		// 3. zapisanie zmian w bazie danych - zmiana formatu na rData,
		$log->info('Zapisuję zmiany w bazie danych...'); 
		$this->format = self::FORMAT_RDATA;
		$this->reader_params = new stdClass;
		$this->reader_params->dfName = 'analysis_data';
		$this->save();

		// 4. Dodawnanie kolumn i poziomów
		$log->info('Przetwarzam klasy i poziomy...');
		foreach($result->variables as $key => $variable) {
			$column = new App_R_DataSet_Column();
			$column->index = $column->label = $column->label_short = $key;
			$column->type = $variable['class'];

			foreach($variable['levels'] as $levelValue) {
				$level = new App_R_DataSet_ColumnLevel();
				$level->value = $levelValue;
				$column->levels->add($level);
			}
			$this->columns->add($column);
			$log->info('Przetworzyłem kolumnę: '.$key);
		}
		
		// 5. Zapisanie całego zestawu danych
		$log->info('Zapisuję zestaw danych');
		try {
			$this->save();
		} catch (Exception $e) {
			$log->err($e->getMessage());
			$log->err($e->getTraceAsString());
			$log->info('Zakończyłem działanie z powodu powyższych błędów');
			return $this->status(self::STATUS_DIRTY);
		}
		$log->info('Przetwarzanie zakończone sukcesem!');
		return $this->status(self::STATUS_READY);
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
		if($save == true) {
			$this->save();
		}

		return $status;
	}

	public function getLoadStatement($var='dane') {
		$path = '../../datasets/' . $this->filename;
		return $this->format == self::FORMAT_CSV ? sprintf('%s=read.csv2("%s")',$var,$path) : sprintf('load("%s")',$path);
	}

	public function delete(Doctrine_Connection $conn = null) {
		$wSettings = Zend_Registry::get('webir');
		@unlink($wSettings['datasetsPath'] . DS . $this->filename);

		return parent::delete($conn);
	}
}