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
 * @package    Controller
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: WebirController.php 396 2010-06-10 13:28:09Z argasek $
 */

/**
 * WebiR project main controller class.
 *
 * @todo This class probably needs to be split and reorgnized in the future!
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class WebirController extends Webir_Controller_Subpage {

	public function init() {
		parent::init();
		if(!isset($this->_session->analysis_data_set)) {
			$this->_session->analysis_data_set = App_R_DataSet::getDefault();
		}

		if(!isset($this->_session->analysis_variables)) {
			$this->_session->analysis_variables = new Doctrine_Collection('App_R_DataSet_Column','id');
		}

		if(!isset($this->_session->analysis_subsets)) {
			$this->_session->analysis_subsets = new Doctrine_Collection('App_R_DataSet_ColumnLevel','id');
		}

		if(!isset($this->_session->adminMode)) {
			$this->_session->adminMode = false;
		}
		$this->view->headScript()->appendFile($this->view->baseUrl($this->view->url(array('action'=>'common'),'js')));
	}

	/**
	 * @desc Strona
	 */
	public function indexAction() {

		$this->_forward('analysis-new');
	}

	/**
	 * @desc Nowa analiza
	 */
	public function analysisNewAction() {
		$this->view->headTitle('Nowa analiza danych');
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/Webir/DataSet.js'));
		$this->_session->analysis_data_set = App_R_DataSet::getDefault();
		$this->_session->analysis_variables = new Doctrine_Collection('App_R_DataSet_Column','id');
		$this->_session->analysis_subsets = new Doctrine_Collection('App_R_DataSet_ColumnLevel','id');
		$this->view->data_set = $this->_session->analysis_data_set;
	}

	/**
	 * @desc Wybór zmiennych do analizy
	 */
	public function analysisChooseVariablesAction() {
		$this->view->headTitle('Wybór zmiennych do analizy');
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/ext-plugins/gridsearch/Ext.ux.grid.Search.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/ext-plugins/gridsearch/Ext.ux.grid.Search-pl.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl($this->view->url(array('action'=>'webir-analysis-choose-variables'),'js')));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/Webir/VarChoose.js'));

		$data_set_id = (int) $this->getRequest()->data_set_id;

		$ds = empty($data_set_id) ? App_R_DataSet::getDefault() : Doctrine::getTable('App_R_DataSet')->find($data_set_id);
		if(!$ds) {
			throw new Webir_Exception('Nie znaleziono domyślnego zestawu danych.');
		}

		if(!$ds->isDefault() && $ds->user_id != $this->_user->id) {
			throw new Webir_Exception('Wybrany zestaw danych nie należy do Ciebie - nie możesz go analizować.');
		}
		$this->_session->analysis_data_set = $ds;
		$this->_session->analysis_variables->clear();
		$this->_session->analysis_subsets->clear();

		$this->view->data_set = $ds;
	}

	/**
	 * @desc Rozpoczęcie zadania (basic)
	 * @return void
	 */
	public function analysisBeginAction() {
		$vars = $this->_session->analysis_variables;
		if($vars->count() == 0) {
			$this->view->error = 'no-variables';
			return false;
		}

		$r_task = new App_R_Task();
		$r_task->function = 'basic';
		$name = (string) $this->getRequest()->name;
		if (!empty($name)) {
			$r_task->name = $name;
		}
		$r_task->user_id = Zend_Registry::get('user')->getAuth()->getIdentity()->id;
		$r_task->data_set_id = $this->_session->analysis_data_set->id;

		$oFun = $r_task->getFunction();
		$oFun->setDataSet($this->_session->analysis_data_set);
		foreach($this->_session->analysis_variables as $var) {
			$oFun->addVariable($var);
		}

		foreach($this->_session->analysis_subsets as $subset) {
			$oFun->addSubset($subset);
		}

//		try {
			$r_task->save();
//		} catch(Exception $e) {
//			throw new Webir_Exception('Nie udało się zapisać zadania');
//		}

		$this->_redirect($this->view->url(array(),'analysis'));
	}

	/**
	 * @desc Wyświetlenie wyniku zadania
	 * @return unknown_type
	 */
	public function analysisResultAction() {
		if($this->getRequest()->style == 'print') {
			$this->view->layout()->setLayout('print',true);
			$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/print.css'), 'all');
		}

		$this->view->headTitle('Rezultat analizy');

		$task_id = (int)$this->getRequest()->task_id;

		/**
		 * A task
		 * @var App_R_Task
		 */
		$task = Doctrine::getTable('App_R_Task')->find($task_id);
		if(!$task || (!$this->_user->isAdmin() && $task->user_id != $this->_user->id)) {
			throw new Webir_Exception('Nie znaleziono zadania');
		}

		/**
		 * Mark the user seen the task.
		 */
		if($task->user_id == $this->_user->id) {
			$task->updateSeen();
		}
		$this->view->style = $this->getRequest()->style;
		$this->view->user = $this->_user;
		$this->view->resultData = $task->getFunction()->getResult();
		$taskInfo = $task->getFunction()->getInfo();
		$this->view->taskInfo = $taskInfo;
		$this->view->variables = $taskInfo->variables;
		$this->view->subsets = $taskInfo->subsets;
		$this->view->analysis_name = $task->name;
		$this->view->analysis_id = $task_id;
	}

	/**
	 * @desc Pobieranie wykresu
	 * @return unknown_type
	 */
	public function getChartAction() {
		$task_id = (int)$this->getRequest()->task_id;

		// FIXME! Needs better filtering (potential security risk)
		$imageFileName = (string) basename($this->getRequest()->filename) . '.png';

		/**
		 * Task object
		 * @var App_R_Task
		 */
		$task = Doctrine::getTable('App_R_Task')->find($task_id);
		if(!$task || (!$this->_user->isAdmin() && $task->user_id != $this->_user->id)) {
			throw new Webir_Exception('Nie znaleziono zadania');
		}

		$imageFilePath = Webir_R::getTasksPath() . DS . $task->directory . DS . $imageFileName;

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		// At the moment, we use these values as default
		$this->_contentType = 'image/png';
		$this->_contentOutputCharset = mb_http_output();
		// Set the default output type and charset
		$this->getResponse()->setHeader('Content-Type', $this->_contentType);
//		$this->getResponse()->setHeader();
		echo file_get_contents($imageFilePath);
	}

	/**
	 * @desc Lista analiz
	 */
	public function analysisAction() {
		$this->view->headTitle('Wyniki analiz');
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/Webir/Task.js'));
	}

	/**
	 *
	 * @desc Analiza zaawansowana
	 */
	public function analysisAdvanceAction() {
		$this->view->headTitle('Analiza zaawansowana');
		$this->view->headScript()->appendFile($this->view->baseUrl($this->view->url(array('action'=>'webir-analysis-choose-variables'),'js')));
		$this->view->headScript()->appendFile($this->view->url(array('action' => 'advanced-common'), 'js'));

		$this->view->headScript()->appendFile($this->view->baseUrl('/js/ext-plugins/gridddroworder/ext.ux.dd.roworder.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/ext-plugins/gridsearch/Ext.ux.grid.Search.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/ext-plugins/gridsearch/Ext.ux.grid.Search-pl.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/Webir/DataSet.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/Webir/Advance.VarChoose.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/Webir/Advance.js'));

		if(!empty($this->getRequest()->data_set_id)) {

			$ds = Doctrine::getTable('App_R_DataSet')->find((int)$this->getRequest()->data_set_id);
			if(!$ds) {
				throw new Webir_Exception('Nie znaleziono zbioru danych');
			}

			if($ds->user_id !== $this->_user->id && !$this->_user->isAdmin()) {
				throw new Webir_Exception('Wybrany zbiór danych nie należy do ciebie.');
			}
			$this->_session->analysis_data_set = $ds;
//			$this->_redirect($this->view->url(array(),'analysis-advance'));
//			die();
		}

		$this->_session->analysis_variables = new Doctrine_Collection('App_R_DataSet_Column','id');
		$this->_session->analysis_subsets = new Doctrine_Collection('App_R_DataSet_ColumnLevel','id');

		$this->view->data_set = $this->_session->analysis_data_set;
	}
}