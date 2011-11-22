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
 * @version    $Id: Anova.php 327 2010-04-19 09:32:50Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Anova extends App_R_Function {
	public function init() {
		$this->_viewScript = $this->_resultScript = 'advance/anova.phtml';
		$this->addFile('common/homogeneity.R');
		$this->addFile('common/srednie_fn.R');
		$this->addFile('common/anova_post-hoc.R');
		
		$this->setParam('post-hoc',true);
	}
	
	protected function _parseResult() {
		parent::_parseResult();
		
		if(!file_exists($this->getTask()->getTaskPath() . DS . 'anova.csv')) {
			$this->_result->errors[] = 'Nie znaleziono pliku wynikowego: anova.csv';
		} else {
			$f = fopen($this->getTask()->getTaskPath() . DS . 'anova.csv','r');
			$this->_result->statistics = array();
			while($row = fgetcsv($f,0,';','"')) {
				if(empty($row[0])) {continue;}
				$this->_result->statistics[] = array($row[0], preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[1]));
			}
			fclose($f);
		}
		
		$params = $this->getInfo()->params;
		if($params['post-hoc'] == true) {
			if(!file_exists($this->getTask()->getTaskPath() . DS . 'compare.csv')) {
				$this->_result->errors[] = 'Brak pliku wynikowego: compare.csv';
			} else {
				$f = fopen($this->getTask()->getTaskPath() . DS . 'compare.csv','r');
				$this->_result->compare = array();
				while($row = fgetcsv($f,0,';','"')) {
					if(empty($row[0])) {continue;}
					$name = array_shift($row);
					$tmpRow = array($name);
					foreach($row as $val) {
						$tmpRow[] = preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$val);
					}
					$this->_result->compare[] = $tmpRow;
				}
			
				if(file_exists($this->getTask()->getTaskPath() . DS . 's_eff.csv')) {
					$f = fopen($this->getTask()->getTaskPath() . DS . 's_eff.csv','r');
					$this->_result->s_eff = array();
					while($row = fgetcsv($f,0,';','"')) {
						if(empty($row[0])) {continue;}
						$this->_result->s_eff = preg_replace('/(-?\d+)?,(\d+)?(e-?\d+)?/','$1.$2$3',$row[1]);
					}
				}
			}
		}
		// dołączamy analizę średnich
		$fSrednie = App_R_Function::factory('srednie',array('task'=>$this->getTask()));
		$rSrednie = $fSrednie->getResult();
		
		$this->_result->errors = array_merge($this->_result->errors,$rSrednie->errors);
		if(isset($rSrednie->srednie)) {
			$this->_result->srednie = $rSrednie->srednie;
		}
		
		return $this->_result;
	}
}