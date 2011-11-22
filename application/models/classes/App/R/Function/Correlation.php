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
 * @version    $Id: Correlation.php 279 2010-04-15 09:36:16Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Correlation extends App_R_Function {
	public function init() {
		$this->_viewScript = $this->_resultScript = "advance/correlation.phtml";
		$this->addFile('common/parametric.R');
		$this->addFile('common/correlations.R');
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		
		if(!file_exists($this->getTask()->getTaskPath() . DS . 'korelacja.csv')) {
			$this->_result->errors[] = 'Brak pliku wynikowego: korelacja.csv';
			return $this->_result;
		}

		$this->_result->statistics = array();
		$f = fopen($this->getTask()->getTaskPath() . DS . 'korelacja.csv','r');
		while($row = fgetcsv($f,0,';','"')) {
			if(empty($row[0])) {continue;}
			$this->_result->statistics[] = $row;
		}
		
		return $this->_result;
	}
}