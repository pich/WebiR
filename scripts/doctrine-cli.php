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

define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'cli');

require_once '../public/initenv.php';

Doctrine::debug(true);

$bootstrap = $application->bootstrap('doctrinecli')->getBootstrap();

$options = Doctrine_Manager::getInstance()->getCurrentConnection()->getOptions();

echo 'Current environment: ' . APPLICATION_ENV . "\n";
echo 'Current connection: ' . $options['dsn']. "\n";

$bootstrap->getResource('doctrinecli')->run($_SERVER['argv']);
