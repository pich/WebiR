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
 * @package    App_R_Function_Chart
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Rozrzut.php 323 2010-04-19 07:49:54Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_Function_Chart_Rozrzut extends App_R_Function_Chart {
	public function init() {
		parent::init();
		$this->_viewScript = 'advance/chart/rozrzut.phtml';

		$this->setParam('jitter_x','FALSE');
		$this->setParam('jitter_y','FALSE');
		$this->setParam('ellipse','FALSE');
		$this->setParam('span',0.5);
		$this->setParam('color_1',1);
		$this->setParam('color_2',8);
	}
}