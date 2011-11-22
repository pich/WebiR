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
 * @package    Webir_Application_Resource
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Doctrine.php 59 2010-03-27 22:34:53Z dbojdo $
 */

/**
 * Doctrine resource
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract {

	public function init() {
		return $this->getDoctrine();
	}

	public function getDoctrine() {
		// Set Doctrine configuration options (like model loading, etc.)
		foreach ($this->_options['attr'] as $key => $value) {
			Doctrine_Manager::getInstance()->setAttribute(constant('Doctrine::' . $key), $value);
		}

		/**
		 * The array of profilers for all databases
		 * 
		 * @var array[string]Doctrine_Connection_Profiler
		 */
		$profilers = array();

		// Get databases array from configuration
		$databases = $this->getBootstrap()->getOption('db');

		// Iterate trough all database setups
		foreach ($databases as $name => $attributes) {
				
			// We create the DSN string from configuration attributes
			$dsn = sprintf('%s://%s:%s@%s:%s/%s',
				$attributes['adapter'],
				$attributes['username'],
				$attributes['password'],
				$attributes['host'],
				$attributes['port'],
				$attributes['dbname']
			);
			
			// Open a new connection
			$connection = Doctrine_Manager::connection($dsn, $name);
			
			// Add a profiler listener if configuration said so 
			if (isset($attributes['profiler']) && $attributes['profiler'] == 1) {
				$profiler = new Doctrine_Connection_Profiler();
				$profilers[$name] = $profiler;
				$connection->setListener($profiler);
			}

		}

		$doctrineProfilers = array(
			'profilers' => $profilers,
			'loggers' => $this->_options['dqlloggers']
		);
		
		Doctrine_Manager::getInstance()->setParam('profilers', $doctrineProfilers);

		return Doctrine_Manager::getInstance();
	}
}