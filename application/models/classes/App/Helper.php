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
 * @package    App
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Helper.php 284 2010-04-15 11:33:21Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_Helper {
	static public function getPValuLevel($pVal) {
		switch(true) {
			case bccomp($pVal,'0.05') == 1: $pApa = sprintf('= %s',$pVal);break;
			case bccomp($pVal,'0.01') == 1: $pApa = '≤ 0.05';break;
			case bccomp($pVal,'0.001') == 1: $pApa = '≤ 0.01';break;
			default:
				$pApa = '≤ 0.001';
		}
		
		return $pApa;
	}
	
	static public function getDCohenPower($val) {
		switch(true) {
			case bccomp($val,'0.8') == 1: $dc = 'silny';break;
			case bccomp($val,'0.3') == 1: $dc = 'średni';break;
			case bccomp($val,'0.2') == 1: $dc = 'słaby';break;
			default:
				$dc = 'marginalny';
		}
		return $dc;
	}
	
	static public function getEthaPower($val) {
		switch(true) {
			case bccomp($val,'0.1') == 1: $effect = 'silny';break;
			case bccomp($val,'0.4') == 1: $effect = 'średni';break;
			default:
				$effect = 'słaby';
		}
		
		return $effect;
	}
	
	static public function getCorrelationPower($val) {
		switch(true) {
			case bccomp(ltrim($val,'-'),'0.7') == 1: $pow = 'związek bardzo silny';break;
			case bccomp(ltrim($val,'-'),'0.3') == 1: $pow = 'związek o umiarkowanej sile';break;
			default:
				$pow = 'brak związku lub związek bardzo słaby';
		}
		
		return $pow;
	}
}