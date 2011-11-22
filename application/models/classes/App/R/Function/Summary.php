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
 * @package    Webir_Controller_Plugin
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Summary.php 279 2010-04-15 09:36:16Z dbojdo $
 */

/**
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class App_R_Function_Summary extends App_R_Function {

	public function init() {
		$this->_viewScript = $this->_resultScript = 'advance/summary.phtml';
		$this->addFile('common/single.R');
	}

	public function _parseResult() {
		$arResult = parent::_parseResult();

		if(!file_exists($this->getTask()->getTaskPath() . DS . 'result.csv')) {
			$this->_result->errors[] = 'Brak pliku wynikowego: result.csv';
		} else {
			$f = fopen($this->getTask()->getTaskPath() . DS . 'result.csv','r');
			$arData = array();
			while($row = fgetcsv($f,0,';','"')) {
				foreach($row as $k=>$v) {
					$row[$k] = preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$v);
				}
				$arData[] = $row;
			}
			fclose($f);
			array_shift($arData);
			$this->_result->statistics = $arData;
		}

		return $this->_result;
	}
}
