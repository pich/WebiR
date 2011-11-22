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
 * @author     Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: sleeping-process.php 384 2010-04-29 13:26:52Z argasek $
 */

// Define application environment
define('APPLICATION_ENV', 'testing');

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR . 'initenv.php';

$sleeplogFilename = APPLICATION_PATH . DS . 'logs' . DS . 'sleep-%s.txt';

$startTimestamp = date('Ymd-His');
file_put_contents(
	sprintf($sleeplogFilename, $startTimestamp),
	sprintf("START: %s\n", $startTimestamp),
	FILE_APPEND
);

define('SLEEP_TIME', 40);

sleep(SLEEP_TIME);

file_put_contents(
	sprintf($sleeplogFilename, $startTimestamp),
	sprintf("END: %s\n", date('Ymd-His')),
	FILE_APPEND
);

echo 'The process has slept for ' . SLEEP_TIME .' seconds. Unix time: ' . time() . "\n";
