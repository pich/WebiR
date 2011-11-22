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
 * @package    AjaxController
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: AjaxController.php 384 2010-04-29 13:26:52Z argasek $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 */
class AjaxController extends Webir_Controller_Json {
	/**
	 * @var App_User
	 */
	protected $_user;
	protected $_session;

	public function init() {
		parent::init();
		$this->_session = new Zend_Session_Namespace();
		$this->_user = Zend_Registry::get('user')->getAuth()->getIdentity();
	}

	/**
	 * @desc Pobieranie listy zadań
	 * @return void
	 */
	public function gettasksAction() {
		$this->_session->adminMode = ($this->getRequest()->getParam('adminMode') == 'true' && $this->_user->isAdmin());
		try {
			$grid = new App_ExtJS_TaskGrid(array(), $this->getRequest());
			$grid->setParam('t_user_id', $this->_user->id);
			$grid->setParam('adminMode',$this->_session->adminMode);
			$this->_json->setData($grid->load());
		} catch(Exception $e) {
			throw new Webir_Exception('Nie udało się pobrać listy zadań');
		}
	}

	/**
	 * @desc Zmiana statusu oraz nazwy zadania
	 * @return void
	 */
	public function updatetaskAction() {
		$data = Zend_Json::decode($this->getRequest()->data_rows);
		$row = $data[0];

		/**
		 * A task
		 * @var App_R_Task
		 */
		$task = Doctrine::getTable('App_R_Task')->find($row['t_id']);
		if(!$task) {
			throw new Webir_Exception('Nie znaleziono zadania');
		}

		if(isset($row['t_status_id']) && $row['t_status_id'] == App_R_Task::STATUS_CANCELED) {
			if($task->user_id !== $this->_user->id && !$this->_user->isAdmin()) {
				throw new Webir_Exception('Zadanie nie należy do Ciebie. Nie możesz go anulować');
			}
			try {
				$task->cancel();
			} catch(Exception $e) {
				throw new Webir_Exception(sprintf('Anulowanie zadania nie powiodło się. Powód: %s)', $e->getMessage()));
			}
		}

		if(isset($row['t_name'])) {
			$f = new Zend_Filter();
				$f->addFilter(new Zend_Filter_StringTrim());
				$f->addFilter(new Zend_Filter_StripTags());
				$name = $f->filter($row['t_name']);
			$v = new Zend_Validate_StringLength(array('min'=>3,'max'=>128));

			if($v->isValid($name)) {
				$task->name = $name;
				try {
					$task->save();
				} catch(Exception $e) {
					throw new Webir_Exception('Niepoprawna nazwa zadania. Wprowadź do 3 do 128 znaków.)');
				}
			}
		}

		//$this->_json->setData(true);
	}

	/**
	 * @desc Usuwanie zadania
	 * @return void
	 */
	public function deletetaskAction() {
		$data = Zend_Json::decode($this->getRequest()->data_rows);
		foreach($data as $id) {
			/**
			 * Task of $id ID.
			 * @var App_R_Task
			 */
			$task = Doctrine::getTable('App_R_Task')->find($id);
			if(!$task) {continue;}
			if($task->user_id != $this->_user->id && !$this->_user->isAdmin()) {
				throw new Webir_Exception('Nie masz uprawnień do usunięcia zadania: '.$task->name);
			}

			if($task->status() == App_R_Task::STATUS_IN_PROGRESS) {
				throw new Webir_Exception('Zadanie w toku. Nie możesz go usunąć');
			}
			try {
				$task->delete();
			} catch(Exception $e) {
				throw new Webir_Exception('Nie udało się usunąć zadania.');
			}
		}
	}

	/**
	 * @desc Pobieranie listy kolumn
	 * @return void
	 */
	public function getcolumnsAction() {
		$grid = new App_ExtJS_ColumnGrid(array(), $this->getRequest());
//		try {
			$this->_json->setData($grid->load(Doctrine::HYDRATE_SCALAR));
//		} catch(Exception $e) {
//			throw new Webir_Exception('Nie udało się pobrać listy kolumn.');
//		}
	}

	/**
	 * @desc Pobieranie poziomów zmiennej
	 * @return void
	 */
	public function getlevelsAction() {
		$combo = new App_ExtJS_LevelsCombo(array(),$this->getRequest());
		try {
			$this->_json->setData($combo->load(Doctrine::HYDRATE_ARRAY));
		} catch(Exception $e) {
			throw new Webir_Exception('Nie udało się pobrać listy poziomów.');
		}
	}

	/**
	 * @desc Pobieranie listy zmiennych
	 * @return void
	 */
	public function getvariablesAction() {
		$grid = new App_ExtJS_VariableGrid(array(),$this->getRequest());
		//try {
			$this->_json->setData($grid->load());
	//	} catch(Exception $e) {
		//	throw new Webir_Exception('Nie udało się pobrać listy zmiennych.');
		//}
	}

	/**
	 * @desc Pobieranie listy zestawów danych
	 * @return unknown_type
	 */
	public function getdatasetsAction() {
		$this->getRequest()->setParam('user_id', $this->_user->id);
		$grid = new App_ExtJS_DataSetListGrid(array(), $this->getRequest());
		try {
			$this->_json->setData($grid->load(Doctrine::HYDRATE_ARRAY));
		} catch(Exception $e) {
			throw new Webir_Exception('Nie udało się pobrać listy zbiorów danych.');
		}
	}

	/**
	 * @desc Usuwanie zestawu danych
	 * @return void
	 */
	public function deletedatasetAction() {
		$data = Zend_Json::decode($this->getRequest()->data_rows);
		foreach($data as $id) {
			$dataset = Doctrine::getTable('App_R_DataSet')->find($id);
			if(!$dataset) {continue;}
			if(($dataset->user_id != $this->_user->id && !$this->_user->isAdmin()) || $dataset->id == App_R_DataSet::getDefault()) {
				throw new Webir_Exception('Nie masz uprawnień do usunięcia tego zbioru danych: '.$dataset->name);
			}

			if($dataset->status() == App_R_DataSet::STATUS_ANALYSING) {
				throw new Webir_Exception('Zbiór danych jest analizowany, nie możesz go usunąć.');
			}

			try {
				$dataset->delete();
			} catch(Exception $e) {
				throw new Webir_Exception('Nie udało się usunąć zbioru danych');
			}
		}
	}

	/**
	 * @desc Dodaje zmienną do analizy
	 * @return void
	 */
	public function addvariableAction() {
		$data = Zend_Json::decode($this->getRequest()->getParam('data_rows',array()));
		foreach($data as $row) {
			$id = (int)$row['v_id'];
			$var = Doctrine::getTable('App_R_DataSet_Column')->find($id);

			if(!$var || $var->data_set_id != $this->_session->analysis_data_set->id) {
				throw new Webir_Exception('Nie znaleziono zmiennej');
			}

			$this->_session->analysis_variables->add($var);
		}

		$this->_json->setData(array('rows'=>$data));
	}

	/**
	 * @desc Zawężanie obszaru analizy
	 * @return void
	 */
	public function setsubsetAction() {
		$from_id = (int)$this->getRequest()->form;
		$to_id = (int)$this->getRequest()->to;
		$level = Doctrine::getTable('App_R_DataSet_ColumnLevel')->find($to_id);

		if(!$level) {
			throw new Webir_Exception('Nie znaleziono wartości.');
		}

		if($level->column->data_set_id !== $this->_session->analysis_data_set->id) {
			throw new Webir_Exception('Wybrana wartość nie należy do badanego zestawu danych.');
		}

		if($this->_session->analysis_subsets->contains($from_id)) {
			$this->_session->analysis_subsets->remove($from_id);
		}

		$this->_session->analysis_subsets->add($level);
	}

	/**
	 * @desc Wyłączenie zawęzania analizy
	 * @return void
	 */
	public function removesubsetAction() {
//		jeżeli będziemy obsługiwać zawężanie po kilku zmiennych
//		$level_id = (int)$this->getRequest()->id;
//		if($this->_session->analysis_subsets->contains($level_id)) {
//			$this->_session->analysis_subsets->remove($level_id);
//		}

			try {
				$this->_session->analysis_subsets->clear();
			} catch(Exception $e) {
				throw new Webir_Exception('Wyłączenie zawężania analizy nie powidło się.');
			}
	}

	/**
	 * @desc Usuwanie zmiennej z analizy
	 * @return void
	 */
	public function removevariableAction() {
		$data = Zend_Json::decode($this->getRequest()->getParam('data_rows'));
		try {
			foreach($data as $id) {
				$id = (int)$id;
				if($this->_session->analysis_variables->contains($id)) {
					$this->_session->analysis_variables->remove($id);
				}
			}
		} catch (Exception $e) {
			throw new Webir_Exception('Nie udało się usuąć zmiennej z analizy.');
		}
	}

	// do analizy advance

	private function getColumn($key = "column_id") {
		$column_id = (int)$this->getRequest()->{$key};
		$column = Doctrine::getTable('App_R_DataSet_Column')->find($column_id);

		if(!$column) {
			throw new Webir_Exception('Nie znaleziono kolumny');
		}

		if($column->data_set->id != App_R_DataSet::getDefault()->id || !$this->_user->isAdmin()) {
			if($column->data_set->user_id != $this->_user->id) {
				throw new Webir_Exception('Nie masz uprawnień do wybranego zbioru danych.');
			}
		}

		return $column;
	}

	/**
	 * @desc Pobieranie poziomów zmiennej (R)
	 * @return void
	 */
	public function getLevelsRAction() {
		$column = $this->getColumn();

		$result = $column->getLevelsR();
		if(!empty($result->errors)) {
			throw new Webir_Exception('Nie udało się pobrać poziomów zmiennej. Błędy: '.implode(', ',$result->errors));
		}

		$this->_json->setData($result->data);
	}

	/**
	 * @desc Zamiana poziomu na NA (R)
	 * @return void
	 */
	public function advanceChangeNaAction() {
		$column = $this->getColumn();

		$wSettings = Zend_Registry::get('webir');
		$level = $this->getRequest()->level;


		$result = $column->levelToNa($level);
		if($result->success != true) {
			throw new Webir_Exception('Nie udało się usunąć poziomu. Błędy: '.implode(', ',$result->errors));
		}

		$this->_json->setData($result->success);
	}

	/**
	 * @desc Uporządkowanie poziomów zmiennej (R)
	 * @return void
	 */
	public function advanceLevelsOrderAction() {
		$column = $this->getColumn();

		$levels = Zend_Json::decode($this->getRequest()->levels);
		$ordered = $this->getRequest()->ordered == 'true' ? true : false;

		$result = $column->orderLevels($levels,$ordered);
		if($result->success != true) {
			$result->errors[] = $result->messages;
			throw new Webir_Exception('Nie udało się uporządkować kolejności. Błędy: '.implode(', ',$result->errors));
		}

		$this->_json->setData(array('ordered'=>$ordered));
	}

	/**
	 * @desc Edycja danych o zmiennej (zmiana typu, nazwy, itd.)
	 * @return void
	 */
	public function savecolumnAction() {
		$data = Zend_Json::decode($this->getRequest()->data_rows);
		$resultData = array();
		foreach($data as $row) {
			$column_id = (int)$row['c_id'];
			unset($row['c_id']);

			$column = Doctrine::getTable('App_R_DataSet_Column')->find($column_id);
			if (!$column) {
				throw new Webir_Exception('Nie znaleziono kolumny');
			}

			if($column->data_set->user_id != $this->_user->id) {
				if(!$column->data_set->isSystem() || !$this->_user->isAdmin()) {
					throw new Webir_Exception('Nie możesz edytować tego zbioru danych.');
				}
			}

			foreach($row as $key=>$value) {
				if($key == 'c_type') {
					$result = $column->changeType($value);
					if($result->success != true) {
						throw new Webir_Exception('Nie udało się zmieinć typu zmiennej. Błąd: '.implode(', ',$result->errors));
					}
				} else {
					$column->{ltrim($key,'c_')} = empty($value) ? null : $value;
				}
			}

			try {
				$column->save();
				$resultData[] = $column->toArray(false);
			} catch(Exception $e) {
				throw new Webir_Exception('Nie udało się zapisać zmian.');
			}
		}

		$this->_json->setData(array('rows'=>$resultData));
	}

	/**
	 * Rozpoczęcie analizy w trybie advance
	 * @return void
	 */
	public function advanceAnalysisBeginAction() {
		$task = new App_R_Task();

		$dpi = (int)$this->getRequest()->dpi;
		$size = (int)$this->getRequest()->size;
		$postHoc = $this->getRequest()->getParam('postHoc') == 'true' ? 'TRUE' : 'FALSE';
		$jitterX = (int)$this->getRequest()->getParam('jitter_x');
		$jitterY = (int)$this->getRequest()->getParam('jitter_y');
		$span = (float)$this->getRequest()->getParam('span');
		$ellipse = $this->getRequest()->getParam('ellipse') == 'true' ? 'TRUE' : 'FALSE';
		$color_1 = (int)$this->getRequest()->getParam('color_1');
		$color_2 = (int)$this->getRequest()->getParam('color_2');

		$task->user_id = $this->_user->id;
		$task->function = $this->getRequest()->getParam('func');
		$task->data_set_id = $this->_session->analysis_data_set->id;

		$oFun = $task->getFunction();

		if($oFun instanceof App_R_Function_Chart) {
			$arKeys = $this->_session->analysis_variables->getKeys();
			$oFun->setParam('dpi',$dpi);
			$oFun->setParam('size',$size);
			$f = new Zend_Filter_StripTags();
				$etyx = $f->filter($this->getRequest()->getParam('etyx'));
				$etyx = empty($etyx) ? $this->_session->analysis_variables->get($arKeys[0])->label : $etyx;
			$oFun->setParam('etyx',$etyx);

			if($oFun instanceof App_R_Function_Chart_Bar || $oFun instanceof App_R_Function_Chart_Histogram) {
				$etyy = $f->filter($this->getRequest()->getParam('etyy'));
				if(!empty($etty)) {
					$oFun->setParam('etyy',$etyy);
				}
			} else {
				$etyy = $f->filter($this->getRequest()->getParam('etyy'));
				$etyy = empty($etyy) ? $this->_session->analysis_variables->get($arKeys[1])->label : $etyy;
				$oFun->setParam('etyy',$etyy);
			}

			if($oFun instanceof App_R_Function_Chart_Rozrzut) {
				$oFun->setParam('jitter_x',$jitterX);
				$oFun->setParam('jitter_y',$jitterX);
				$oFun->setParam('ellipse',$ellipse);
				$oFun->setParam('span',$span);
				$oFun->setParam('color_1',$color_1);
				$oFun->setParam('color_2',$color_2);
			}
		}

		if($oFun instanceof App_R_Function_Anova) {
			$oFun->setParam('post-hoc',$postHoc);
		}

		$oFun->setDataSet($this->_session->analysis_data_set);
		foreach($this->_session->analysis_variables as $var) {
			$oFun->addVariable($var);
		}

		foreach($this->_session->analysis_subsets as $subset) {
			$oFun->addSubset($subset);
		}

//		try {
			$task->save();
//		} catch(Exception $e) {
//			throw new Webir_Exception('Nie udało się dodać zadania do kolejki.');
//		}

		// wyrzucamy zmienne z analizy
		$this->_session->analysis_subsets->clear();
		$this->_session->analysis_variables->clear();

		$this->_json->setData('Zadanie zostało dodane do kolejki');
	}
}