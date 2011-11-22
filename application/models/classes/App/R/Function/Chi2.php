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
 * @version    $Id: Chi2.php 279 2010-04-15 09:36:16Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Chi2 extends App_R_Function {
	public function init() {
		$this->_viewScript = $this->_resultScript = 'advance/chi2.phtml';
		$this->addFile('common/chi2.R');
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		// 'statistics.csv' - statystyki
		// 'observed.csv' - obserwowane
		// 'observed_pct.csv' - obserwowane procentowe
		// 'observed_pct_r.csv' - obserwowane prcentowe (wiersz)
		// 'observed_pct_c.csv' - obserwowane procentowe (kolumna)
		// 'expected.csv' - oczekiwane
		// 'residuals.csv' - reszty
		
		$arFiles = array('statistics.csv','observed.csv','observed_pct.csv','observed_pct_r.csv','observed_pct_c.csv','expected.csv','residuals.csv');
		$arResources = array();
		foreach($arFiles as $file) {
			if(file_exists($this->getTask()->getTaskPath() . DS . $file)) {
				$arResources[mb_substr($file,0,-4)] = fopen($this->getTask()->getTaskPath() . DS . $file,'r');
			} else {
				$this->_result->errors[] = 'Brak pliku wynikowego: '.$file;
			}
		}

		foreach($arResources as $key=>$resource) {
			while($row = fgetcsv($resource,0,';','"')) {
				foreach($row as $k=>$v) {
					$row[$k] = preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$v);
				}

				$this->_result->{$key}[] = $row;
			}
		}
		
		if(isset($this->_result->statistics)) {array_shift($this->_result->statistics);}
		
		return $this->_result;
	}
}