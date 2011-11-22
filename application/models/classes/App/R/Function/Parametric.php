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
 * @version    $Id$
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Parametric extends App_R_Function {
	public function init() {
		$this->_viewScript = $this->_resultScript = 'advance/parametric.phtml';
		$this->addFile('common/parametric.R');
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		
		if(!file_exists($this->getTask()->getTaskPath() . DS . 'parametric.csv')) {
			$this->_result->errors[] = 'Nie znaleziono pliku wynikowego: parametric.csv';
		} else {
			$f = fopen($this->getTask()->getTaskPath() . DS . 'parametric.csv','r');
			while($row = fgetcsv($f,0,';','"')) {
				if(empty($row[0])) {continue;}
				$this->_result->parametric = $row[1] == "TRUE" ? true : false;
			}
			fclose($f);
		}
		
		if(!file_exists($this->getTask()->getTaskPath() . DS . 'statistics.csv')) {
			$this->_result->errors[] = 'Nie znaleziono pliku wynikowego: statistics.csv';
		} else {
			$f = fopen($this->getTask()->getTaskPath() . DS . 'statistics.csv','r');
			$this->_result->statistics = array();
			while($row = fgetcsv($f,0,';','"')) {
				if(empty($row[0])) {continue;}
				$this->_result->statistics[] = array($row[0], preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[1]),preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[2]));
			}
			fclose($f);
		}
		
		return $this->_result;
	}
}