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
 * @author     Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: index.php 7 2010-02-18 11:41:03Z argasek $
 */


chdir(__DIR__);
if(file_exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webir_processing.lock')) {die();}
define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'cli');
require_once('../public/initenv.php');
$application->bootstrap();
$wSettings = Zend_Registry::get('webir');
Webir_Debug::getInstance()->cli->info('Webir DataSet Processing - start');
try {
	exec('touch ' . sys_get_temp_dir() . DS . 'webir_processing.lock');
	$dql = Doctrine_Query::create()->from('App_R_DataSet ds')
																		->where('ds.status_id = ?',App_R_DataSet::STATUS_WAITING)
																		->orderBy('ds.created_at ASC');

	if(APPLICATION_ENV == 'cli-dev' && isset($wSettings['user_id'])) {
		$dql->addWhere('ds.user_id = ?',$wSettings['user_id']);
	}

	$dataset = $dql->fetchOne();
	if($dataset) {
		Webir_Debug::getInstance()->cli->info('Webir DataSet Processing - file: ' . $dataset->filename . ' / id: ' . $dataset->id);
		$dataset->process();
		Webir_Debug::getInstance()->cli->info('Webir DataSet Processing - file: successfull');
	}
} catch(Exception $e) {
	exec('rm -f ' . sys_get_temp_dir() . DS . 'webir_processing.lock');
	if(isset($dataset)) {
		$dataset->status(App_R_DataSet::STATUS_DIRTY);
	}
	Webir_Debug::getInstance()->cli->err('Webir DataSet Processing - Exception: ' . $e->getMessage());
	Webir_Debug::getInstance()->cli->err('Webir DataSet Processing - Trace: ' . $e->getTraceAsString());
	Webir_Debug::getInstance()->cli->info('Webir DataSet Processing - stop');
}
exec('rm -f ' . sys_get_temp_dir() . DS . 'webir_processing.lock');
Webir_Debug::getInstance()->cli->info('Webir DataSet Processing - stop');