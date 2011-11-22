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
 * @version    $Id: initenv.php 154 2010-04-10 07:03:09Z argasek $
 */

// A minimum PHP interpreter version to run the application.
define('MINIMUM_PHP_VERSION', '5.3.0');

// Basic PHP version check. We need to do it now, because later instructions would cause simple parser Fatal Error.
if (version_compare(phpversion(), MINIMUM_PHP_VERSION, '<')) {
	// Display an error in a more pleasant way, when script's being run from a web browser
	if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');
	exit('WebiR requires PHP version ' . MINIMUM_PHP_VERSION . ' or newer, but your installed version is currently ' . phpversion() . ".\n");
}

// A handful shortcuts for filesystem directory separator and system variable PATH separator
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PS') || define('PS', PATH_SEPARATOR);
// Define the root directory path and the path to an application directory, if it's not defined already
defined('ROOT_PATH') || define('ROOT_PATH', realpath(__DIR__ . DS . '..'));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_PATH . DS . 'application');
// Define the application environment (context), if it's not defined already
defined('APPLICATION_ENV') || define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'production');

set_include_path(
	ROOT_PATH . DS. 'library' . DS . 'zend' . PS .
	get_include_path()
);

require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';

// We load main application configuration file, all sections (null) and we allow to further modify configuration values (true)
try {
	$config = new Zend_Config_Ini(APPLICATION_PATH . DS . 'configs' . DS . 'application.ini', null, true);
	$config->merge(new Zend_Config_Ini(APPLICATION_PATH . DS . 'configs' . DS . 'routing.ini'));
	if (is_file(APPLICATION_PATH . DS . 'configs' . DS . 'user.ini')) {
		$config->merge(new Zend_Config_Ini(APPLICATION_PATH . DS . 'configs' . DS . 'user.ini'));
	}
} catch (Zend_Config_Exception $e) {
	if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');
	exit("WebiR was unable to read the main configuration file (application.ini)" . ".\n");
}

try {
	// Instantiate the application, bootstrap, and run
	$application = new Zend_Application(APPLICATION_ENV, $config->{APPLICATION_ENV});

	$autoloader = $application->getAutoloader();

	// We want the autoloader to load any namespace
	$autoloader->setFallbackAutoloader(false);
	// We don't want to be informed about missing classes in a clear way
	$autoloader->suppressNotFoundWarnings(true);
// Catch any uncaught exceptions
} catch (Exception $e) {
	if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');
	exit("WebiR error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
}
