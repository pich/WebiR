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
 * @package    Webir_R_Function
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Abstract.php 334 2010-04-19 11:58:31Z dbojdo $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
abstract class Webir_R_Function_Abstract {
	/**
	 *
	 * @var Zend_View
	 */
	protected $_view;

	/**
	 *
	 * @var string
	 */
	protected $_viewScript;
	
	protected $_resultScript;
	
	protected $_data_set;
	protected $_variables;
	protected $_subsets;
	protected $_mode = 'advance';
	
	protected $_result;
	
	/**
	 *
	 * @var Webir_R
	 */
	protected $_task;

	/**
	 * @var Array
	 */
	protected $_params = array();

	/**
	 *
	 * @var Array
	 */
	protected $_files = array();

	public function __construct(Array $options = array()) {
		if(isset($options['params']) && is_array($options['params'])) {
			array_merge($this->_params,$options['params']);
		}

		if(isset($options['files']) && is_array($options['files'])) {
			array_merge($this->_files,$options['files']);
		}

		$this->_task = isset($options['task']) ? $options['task'] : $this->_task = new Webir_R(array('-f',escapeshellcmd('script.R'))); 
		$this->_mode = isset($options['mode']) ? $options['mode'] : 'advance';
		
		$this->_variables = new Doctrine_Collection('App_R_DataSet_Column');
		$this->_subsets = new Doctrine_Collection('App_R_DataSet_ColumnLevel');
		
		$this->_result = new Webir_R_Function_Result();
		
		if(isset($options['data_set'])) {
			$this->_data_set = $options['data_set'];
		}
		
		if(isset($options['variables'])) {
			foreach($options['variables'] as $var) {
				$this->_variables->add($var);
			}
		}
		
		$this->_view = new Zend_View(array('basePath'=>'../application/views/r/'));
		$this->_view->function = $this;
		$this->_view->webir_settings = Zend_Registry::get('webir');
		
		$this->init();
	}

	public function init() {}
	
	/**
	 * 
	 * @return Doctrine_Collection
	 */
	public function getVariables() {
		return $this->_variables;
	}
	
	/**
	 * 
	 * @return Doctrine_Collection
	 */
	public function getSubsets() {
		return $this->_subsets;
	}
	
	/**
	 * 
	 * @param App_R_DataSet_Column $var
	 * @return Webir_R_Function_Abstarct
	 */
	public function addVariable(App_R_DataSet_Column $var) {
		$this->_variables->add($var);
		return $this;
	}
	
	/**
	 * 
	 * @param App_R_DataSet_Level $level
	 * @return Webir_R_Function_Abstarct
	 */
	public function addSubset(App_R_DataSet_ColumnLevel $level) {
		$this->_subsets->add($level);
		return $this;
	}
	
	/**
	 * 
	 * @return App_R_DataSet
	 */
	public function getDataSet() {
		return $this->_data_set;
	}
	
	/**
	 * @return Webir_R_Function_Abstract
	 */
	public function setDataSet(App_R_DataSet $dataset) {
		$this->_data_set = $dataset;
		return $this;
	}
	
	public function getMode() {
		return $this->_mode;
	}
	
	/**
	 * 
	 * @param string $mode
	 * @return Webir_R_Function_Abstract
	 */
	public function setMode($mode) {
		$this->_mode = $mode;
		return $this;
	}
	
	/**
	 * 
	 * @return Webir_R
	 */
	public function getTask() {
		return $this->_task;
	}

	public function getView() {
		return $this->_view;
	}

	public function setParam($key,$value) {
		$this->_params[$key] = $value;
		return $this;
	}

	public function getParam($key) {
		if(!array_key_exists($key,$this->_params)) {
			throw new Webir_Exception('Nie znaleziono parametru: '.$key);
		}

		return $this->_params[$key];
	}

	public function getViewScript() {
		return $this->_viewScript;
	}

	public function setViewScript($viewScript) {
		$this->_viewScript = $viewScript;
		return $this;
	}
	
	public function getResultScript() {
		return $this->_resultScript;
	}
	
	public function setResultScript($resultScript) {
		$this->_resultScript = $resultScript;
		return $this;
	}
	
	public function addFile($file) {
		$this->_files[] = $file;
		return $this;
	}

	public function getFiles() {
		return $this->_files;
	}

	/**
	 * Internal function, parsed result files
	 * @return Webir_R_Function_Result
	 */
	protected function _parseResult() {
		$this->_result->output = @file_get_contents($this->getTask()->getTaskPath() . DS . 'output.txt');
		$this->_result->messages = @file_get_contents($this->getTask()->getTaskPath() . DS . 'message.log');
		
		return $this->_result;
	}
	
	/**
	 * Parse files and save serialize result object 
	 * @return boolean
	 */
	public function parseResult() {
		@unlink($this->getTask()->getTaskPath() . DS . 'resultData.txt');
		file_put_contents($this->getTask()->getTaskPath() . DS . 'resultData.txt',serialize($this->_parseResult()));

		return true;
	}
	
	public function getResult($force=false) {	
		if($force == true || !file_exists($this->getTask()->getTaskPath() . DS . 'resultData.txt')) {
			$this->parseResult();
		}
		
		return unserialize(file_get_contents($this->getTask()->getTaskPath() . DS . 'resultData.txt'));
	}
	
	public function runTask($back=false) {
		$this->createScript();
		$this->_task->run($back);
	}

	public function createScript() {
		$this->_validate(true);
		
		file_put_contents($this->_task->getTaskPath() . DS . 'analysis.txt',serialize($this->_createInfo()));
		$s = $this->_view->render('function.phtml');
		file_put_contents($this->_task->getTaskPath() . DS . 'script.R',$s);
		return md5($s);
	}
	
	protected function _validate($throwException = false) {
		return true;
	}
	
	public function isValid() {
		return $this->_validate(false);
	}
	
	protected function _createInfo() {
		$function = new stdClass();
			$function->variables = array();
			$function->subsets = array();
			$function->data_set = $this->_data_set->toArray();
			$function->mode = $this->_mode;
			$function->params = $this->_params;
			$function->resultScript = $this->_resultScript;
			
		foreach($this->_variables as $variable) {
			$function->variables[] = $variable->toArray();	
		}
		
		foreach($this->_subsets as $subset) {
			$arSubset['var'] = $subset->column->toArray();
			$arSubset['level'] = $subset->toArray();
			$function->subsets[] = $arSubset;
		}
		
		return $function;
	}
	
	public function getInfo() {
		return unserialize(file_get_contents($this->_task->getTaskPath() . DS . 'analysis.txt'));
	}
}