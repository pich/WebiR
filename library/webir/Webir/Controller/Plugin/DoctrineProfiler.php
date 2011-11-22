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
 * @package    Webir_Controller_Plugin
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: DoctrineProfiler.php 9 2010-03-12 15:24:30Z argasek $
 */

/**
 * Doctrine profiler controller plugin
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Controller_Plugin_DoctrineProfiler extends Zend_Controller_Plugin_Abstract {

	public function postDispatch(Zend_Controller_Request_Abstract $request) {
		$profilers = array();
		$loggers = array();
		
		list($profilers, $loggers) = Doctrine_Manager::getInstance()->getParam('profilers');

		foreach ($profilers as $key => $profiler) {
			$info = $this->_getQueryInfo($profiler);
			Webir_Debug::table(
				sprintf(
					'Total SQL queries time (%s): %.2f, number of instructions/queries: %d/%d',
					$key,
					$info['timeTotal'],
					$info['count'],
					$info['executeCount']
				),
				$info['sql'],
				(array) $loggers
			);
		}
	}

	private function _getQueryInfo(Doctrine_Connection_Profiler $profiler) {
		$time = 0;
		$count = 0;
		$executeCount = 0;

		$sql = array();
		$sql[] = array('Action', 'SQL Query', 'Time', 'Arguments');
		foreach ($profiler as $event) {
			$count++;
			if ($event->getName() === 'execute') {
				$executeCount++;
			}
			$time += $event->getElapsedSecs();
			$sql[] = array(
				$event->getName(),
				$event->getQuery(),
				sprintf("%f", $event->getElapsedSecs()),
				$event->getParams()
			);
		}

		return array(
			'timeTotal' => $time,
			'count' => $count,
			'executeCount' => $executeCount,
			'sql' => $sql
		);
	}
}