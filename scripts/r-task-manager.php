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
 * @package    Scripts
 * @author     Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @author     Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: index.php 7 2010-02-18 11:41:03Z argasek $
 */

chdir(__DIR__);

$lockfilename = 'webir_manager.lock';
$lockpath = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
$lockfile = $lockpath . $lockfilename;

// No need to bootstrap the whole engine when lock file exists
if (is_file($lockfile)) {
	$lockfile_exists_msg = '';
	// $lockfile_exists_msg = sprintf("%s exists in %s, quitting\n", $lockfilename, $lockpath);
	die($lockfile_exists_msg);
} else {
	// echo "Creating $lockfile ...\n";
}

define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'cli');
require_once('../public/initenv.php');

$application->bootstrap();

$log = Webir_Debug::getInstance()->cli;
$log->info(sprintf('Webir_Process_Manager - start (APPLICATION_ENV: %s)', APPLICATION_ENV));

try {
	file_put_contents($lockfile, '');
	$manager = Webir_Process_Manager::getInstance();
	$manager->setLogger($log);
	$arStats = $manager->manage();
	$log->info('Webir_Process_Manager - raport');
	$log->info(sprintf('Uruchomiono: %d, Zakończono: %d, Przerwano: %d',$arStats['new'],$arStats['success'],$arStats['canceled']));
} catch (Exception $e) {
	@unlink($lockfile);
	$log->err('Webir_Process_Manager - Exception: ' . $e->getMessage());
	$log->err('Webir_Process_Manager - Trace: ' . $e->getTraceAsString());
	$log->err('Webir_Process_Manager - stop');
}

@unlink($lockfile);
$log->info('Webir_Process_Manager - stop');