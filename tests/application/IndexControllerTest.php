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
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: IndexControllerTest.php 7 2010-02-18 11:41:03Z argasek $
 */

/**
 * Index Controller test case
 * @author Jakub Argasiński
 *
 */
class IndexControllerTest extends BaseControllerTestCase {

	public function testIndexAction() {
		$this->dispatch('/');
		$this->assertController('index');
		$this->assertAction('index');
		$this->assertResponseCode(200);
	}

}