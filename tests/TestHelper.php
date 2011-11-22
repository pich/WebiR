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
 * @package    Webir
 * @author     Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: TestHelper.php 7 2010-02-18 11:41:03Z argasek $
 */

// Define application environment
define('APPLICATION_ENV', 'testing');

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR . 'initenv.php';

/**
 * Base controller test case class
 *
 */
abstract class BaseControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase {
	public function setUp() {
		global $application;
		$this->bootstrap = array($application->getBootstrap(), 'bootstrap');
		return parent::setUp();
	}

	public function tearDown() {
		// Tear Down Routine
	}
}