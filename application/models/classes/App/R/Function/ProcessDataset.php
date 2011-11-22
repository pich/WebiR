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
 * @version    $Id: ProcessDataset.php 279 2010-04-15 09:36:16Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_ProcessDataSet extends App_R_Function {
	public function init() {
		$this->_viewScript = 'advance/process_dataset.phtml';
		$this->addFile('common/classes.R');
		$this->addFile('common/levels.R');
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		
		$info = $this->getInfo();
		if(!file_exists($this->getTask()->getTaskPath() . DS . $info->data_set['filename'])) {
			$this->_result->errors[] = "Nie znaleziono pliku zestawu danych: ".$info->data_set['filename'];
		}
		
		if(!file_exists($this->getTask()->getTaskPath() . DS . 'classes.csv')) {
			$this->_result->errors[] = "Nie znaleziono pliku wynikowego: classes.csv";
		}
		
		if(!file_exists($this->getTask()->getTaskPath() . DS . 'levels.csv')) {
			$this->_result->errors[] = "Nie znaleziono pliku wynikowego: levels.csv";
		}
	
		if(count($this->_result->errors)) {
			return $this->_result;
		}
		
		$cLevels = fopen($this->getTask()->getTaskPath() . DS . 'classes.csv','r');
		$this->_result->levels = array();
		while($row = fgetcsv($cLevels, 0, ';','"', "\\")) {
			if(empty($row[0])) {continue;}
			$this->_result->variables[array_shift($row)] = array('class'=>implode($row),'levels'=>array());
		}
		
		$fLevels = fopen($this->getTask()->getTaskPath() . DS . 'levels.csv','r');
		$this->_result->levels = array();
		while($row = fgetcsv($fLevels, 0, ';','"', "\\")) {
			$empty = array('NA','');
			if(empty($row[0]) || !isset($row[1]) || in_array($row[1],$empty)) {continue;}
			$key = array_shift($row);
			$arLevels = array_diff($row,$empty);

			if(!empty($arLevels)) {
				$this->_result->variables[$key]['levels'] = $arLevels;
			}
		}
		
		return $this->_result;
	}
}