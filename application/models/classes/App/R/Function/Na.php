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
 * @version    $Id: Na.php 323 2010-04-19 07:49:54Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Na extends App_R_Function {
	public function init() {
		$this->_viewScript = 'advance/na.phtml';
	}
	
	protected function _parseResult() {
		$arResult = parent::_parseResult();
		$resultFile = $this->_task->getTaskPath() . DS . 'result.csv';
		if (!is_file($resultFile)) {
			$this->_result->errors[] = 'Nie znaleziono pliku wynikowego: result.csv';
			return $this->_result;
		}
		
		$f = fopen($resultFile, 'r');
		while($row = fgetcsv($f,0,';','"')) {
			if(empty($row[0])) {continue;}
			$this->_result->success = $row[1] == "TRUE" ? true : false;
		}
		
		return $this->_result;
	}
}