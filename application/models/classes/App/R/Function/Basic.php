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
 * @package    App_R_Function
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Basic.php 368 2010-04-20 14:59:12Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Basic extends App_R_Function {
	protected $_name;
	
	public function init() {
		$this->addFile('basic.R');
	}
	
	public function createScript() {
		$this->_setParams();
		return parent::createScript();
	}
	
	private function _setParams() {
		if($this->_variables->count() == 1) {
			$this->_viewScript = $this->_resultScript = 'basic/summary.phtml';
			return true;
		}
		
		if($this->_variables->count() == 2) {
			// integer vs. integer lub integer vs. ordered factor (korelacja)
			if(($this->_variables->get(0)->isQuantitative() && $this->_variables->get(1)->isQuantitative())) 
			{
				$this->_viewScript = $this->_resultScript = 'basic/correlation.phtml';
				$this->setParam('forced',false);
				return true;
			}
			
			// factor vs. factor (chi2)
			if(($this->_variables->get(0)->type == App_R_DataSet_Column::TYPE_FACTOR && !$this->_variables->get(0)->isOrdered() &&  
				 $this->_variables->get(1)->type == App_R_DataSet_Column::TYPE_FACTOR && !$this->_variables->get(1)->isOrdered()) ||
				 ($this->_variables->get(0)->type == App_R_DataSet_Column::TYPE_FACTOR && !$this->_variables->get(0)->isOrdered() &&  
				 $this->_variables->get(1)->type == App_R_DataSet_Column::TYPE_FACTOR && $this->_variables->get(1)->isOrdered()) ||
				 ($this->_variables->get(0)->type == App_R_DataSet_Column::TYPE_FACTOR && $this->_variables->get(0)->isOrdered() &&  
				 $this->_variables->get(1)->type == App_R_DataSet_Column::TYPE_FACTOR && !$this->_variables->get(1)->isOrdered())
				 )
			{
				$this->_viewScript = $this->_resultScript = 'basic/chi2.phtml';
				return true;
			}
			
			// ordered factor vs. integer (korelacja)
			if(($this->_variables->get(0)->type == App_R_DataSet_Column::TYPE_FACTOR && $this->_variables->get(0)->isOrdered() &&  
				 $this->_variables->get(1)->isQuantitative()) || ($this->_variables->get(0)->isQuantitative() && 
				 $this->_variables->get(1)->type == App_R_DataSet_Column::TYPE_FACTOR && $this->_variables->get(1)->isOrdered()))
			{
				$this->_viewScript = $this->_resultScript = 'basic/correlation.phtml';
				return true;
			}
			
			// ordered factor vs. ordered factor (korelacja nieparametryczna)
			if($this->_variables->get(0)->type == App_R_DataSet_Column::TYPE_FACTOR && $this->_variables->get(0)->isOrdered() &&  
				 $this->_variables->get(1)->type == App_R_DataSet_Column::TYPE_FACTOR && $this->_variables->get(1)->isOrdered())
			{
				$this->_viewScript = 'basic/correlation_nonparam.phtml';
				$this->_resultScript = 'basic/correlation.phtml';
				return true;
			}
			
			// ilościowa vs. factor (średnie)
			if(($this->_variables->get(0)->isQuantitative() && $this->_variables->get(1)->type == App_R_DataSet_Column::TYPE_FACTOR && !$this->_variables->get(1)->isOrdered()) 
				 ||
				 ($this->_variables->get(1)->isQuantitative() && $this->_variables->get(0)->type == App_R_DataSet_Column::TYPE_FACTOR && !$this->_variables->get(0)->isOrdered())
				)
			{
				$this->_viewScript = $this->_resultScript = 'basic/srednie.phtml';
				$this->setParam('post-hoc',true);
				return true;
			}
			
			throw new Webir_Exception('Nie znaleziono funkcji pasującej do analizy wybranych zmiennych');
		}
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		$info = $this->getInfo();
		switch($info->resultScript) {
			case 'basic/chi2.phtml':$result = $this->_getChi2Result();break;
			case 'basic/srednie.phtml':$result = $this->_getSrednieResult();break;
			case 'basic/correlation.phtml':$result = $this->_getCorrelationResult();break;
			case 'basic/summary.phtml':$result = $this->_getSummaryResult();break;
			default:
				throw new Webir_Exception('Nie przypisano wyniku.');
		}
		
		return $result;
	}

	private function _getChi2Result() {
		$f = new App_R_Function_Chi2(array('task'=>$this->getTask()));
		$this->_result = $f->getResult(true);
			$oFun = App_R_Function::factory('chart_Bar',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-slupkowy');
			$oFun->setParam('chartName','');
			$oFun->setParam('arLeg',array(array(2,3)));
			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);
			
			$oFun = App_R_Function::factory('chart_Poletka',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-poletka');
			$oFun->setParam('chartName','');
			$oFun->setParam('arLeg',array(array(0,1)));
			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);
			
		return $this->_result;
	}
	
	private function _getSummaryResult() {
		$f = new App_R_Function_Summary(array('task'=>$this->getTask()));
		$this->_result = $f->getResult(true);
		
		// wykres słupkowy i kołowy
		if($this->getInfo()->variables[0]['type'] == 'factor') {
			$oFun = App_R_Function::factory('chart_Bar',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-slupkowy');
			$oFun->setParam('chartName',$this->getInfo()->variables[0]['index']);
			$result = $oFun->getResult(true);
			
			$this->_result->charts[] = array_shift($result->charts);
			$oFun = App_R_Function::factory('chart_Kolowy',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-kolowy');
			$oFun->setParam('chartName',$this->getInfo()->variables[0]['index']);
			$oFun->setParam('arLeg',array(array(1)));
			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);
		} else {
			$oFun = App_R_Function::factory('chart_Histogram',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-histogram');
			$oFun->setParam('chartName',$this->getInfo()->variables[0]['index']);
			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);	
		}
		
		return $this->_result;
	}
	
	private function _getCorrelationResult() {
		$f = new App_R_Function_Correlation(array('task'=>$this->getTask()));
		$this->_result = $f->getResult(true);
		
		$this->_result->charts = array();

		// korelacja nieparametryczna (ordered factor vs. ordered factor)
		if($this->getInfo()->variables[0]['is_ordered'] == true && $this->getInfo()->variables[1]['is_ordered'] == true) {
			$this->_result = $f->getResult(true);
			
			$oFun = App_R_Function::factory('chart_Bar',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-slupkowy');
			$oFun->setParam('chartName','');
			$oFun->setParam('arLeg',array(array(2,3)));
			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);
			
			$oFun = App_R_Function::factory('chart_Poletka',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-poletka');
			$oFun->setParam('chartName','');
			$oFun->setParam('arLeg',array(array(0,1)));
			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);
		} else {
			// wykres rozrzutu
			$oFun = App_R_Function::factory('chart_Box',array('task'=>$this->getTask()));
			$oFun->setParam('chartPrefix','wykres-rozrzut');
			$oFun->setParam('chartName','');
			$oFun->setParam('arLeg',array(array(0,1)));

			$result = $oFun->getResult(true);
			$this->_result->charts[] = array_shift($result->charts);
		}
		
		return $this->_result;
	}
	
	private function _getSrednieResult() {
		$arFiles = array('anova.csv','tstudent.csv','kraskal.csv','wilcoxon.csv');

		// sprawdzamy jaki test został wykonany
		$this->_result->test = null;
		foreach($arFiles as $file) {
			if(file_exists($this->getTask()->getTaskPath() . DS . $file)) {
				$this->_result->test = mb_substr($file,0,-4);
				break;
			}
		}

		if(empty($this->_result->test)) {
			$this->_result->errors[] = 'Nie znaleziono żadnego z plików wynikowych: '.implode(', ',$arFiles);
			return $this->_result;
		}
		
		$info = $this->getInfo();
		$oFun = App_R_Function::factory($this->_result->test,array('task'=>$this->getTask(),'params'=>$info->params));
		$result = $oFun->getResult(true);
		$result->test = $this->_result->test;
		$result->errors = array_merge($result->errors,$this->_result->errors);
		$this->_result = $result;

		// wykres skrzynkowy
		$oFun = App_R_Function::factory('chart_Box',array('task'=>$this->getTask()));
		$oFun->setParam('chartPrefix','wykres-skrzynki');
		$oFun->setParam('chartName','');
		$oFun->setParam('arLeg',array(array(0,1)));
		$result = $oFun->getResult(true);
		$this->_result->charts[] = array_shift($result->charts);
		
		// wykres srednich
		$oFun = App_R_Function::factory('chart_BarSrednie',array('task'=>$this->getTask()));
		$oFun->setParam('chartPrefix','wykres-slupkowy-srednie');
		$oFun->setParam('chartName','');
		$oFun->setParam('arLeg',array(array(2,3)));
		$result = $oFun->getResult(true);
		$this->_result->charts[] = array_shift($result->charts);
		
		return $this->_result;	
	}
}