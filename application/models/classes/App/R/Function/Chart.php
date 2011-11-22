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
 * @category   APp
 * @package    App_R_Function
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Chart.php 336 2010-04-19 12:14:57Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Chart extends App_R_Function {
	public function init() {
		$this->_resultScript = "advance/chart.phtml";
		$this->addFile('common/legend.R');
		$this->addFile('common/charts.R');
		
		$this->setParam('dpi',72);
		$this->setParam('wielkosc',6);
		$this->setParam('etyx','Etykieta X');
		$this->setParam('etyy','Etykieta Y');
		$this->setParam('chartPrefix','chart');
		$this->setParam('chartName','');
	}
	
	protected function _parseLegend() {
		$arLegend = array();
		if(file_exists($this->getTask()->getTaskPath() . DS . 'legenda.csv')) {
			$f = fopen($this->getTask()->getTaskPath() . DS . 'legenda.csv','r');
			$this->_result->statistics = array();
			$i=-1;
			while($row = fgetcsv($f,0,';','"')) {
				if(empty($row[0])) {$i++; $arLegend[$i] = array();continue;}
				$arLegend[$i][] = sprintf('%s - %s',$row[0],$row[1]);
			}
			fclose($f);
		}
		
		return $arLegend;
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		
		$info = $this->getInfo();
		$arLeg = isset($this->_params['arLeg']) ? $this->getParam('arLeg') : array();
		if(empty($arLeg)) {
			foreach($info->variables as $key=>$var) {
				$arLeg[0][] = $key;
			}
		}

		$this->_result->charts = array();
		
		// ustalam prefix pliku
		$chartPrefix = isset($info->params['chartPrefix']) ? $info->params['chartPrefix'] : $this->getParam('chartPrefix');
		$chartName = isset($info->params['chartName']) ? $info->params['chartName'] : $this->getParam('chartName');

		$arLegend = $this->_parseLegend();

		foreach($arLeg as $key=>$legend) {
			if(!file_exists($this->getTask()->getTaskPath() . DS . $chartPrefix . (!empty($chartName) ? ('_' . $chartName) : '').'.png')) {
				$this->_result->charts[] = array('file'=>false,'legend'=>$arLegend[$key]);
			} else {
				foreach($legend as $lIndex) {
					$chartLegend[] = $arLegend[$lIndex];
				}
				$this->_result->charts[] = array('file'=>$chartPrefix . (!empty($chartName) ? ('_' . $chartName) : ''),'legend'=>$chartLegend);
			}
		}
		if($this->_hasInvalidCharts()) {
			$this->_result->errors[] = "Nie znaleziono oczekiwanego pliku wykresu.";
		}

		return $this->_result;
	}
	
	protected function _hasInvalidCharts() {
		if(!isset($this->_result->charts)) {
			return false;
		}
		
		foreach($this->_result->charts as $chart) {
			if($chart['file'] == false) {
				return true;
			}
		}
		
		return false;
	}
}