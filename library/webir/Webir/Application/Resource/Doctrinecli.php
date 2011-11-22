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
 * @version    $Id: Doctrinecli.php 9 2010-03-12 15:24:30Z argasek $
 */

/**
 * Doctrine CLI resource
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Application_Resource_Doctrinecli extends Zend_Application_Resource_ResourceAbstract {
	
	public function init() {
		return $this->getDoctrineCli();
	}

	public function getDoctrineCli() {
		$this->getBootstrap()->bootstrap('doctrine');
		
		$config = array(
			'data_fixtures_path'  =>  $this->_options['fixturesPath'],
			'models_path'         =>  $this->_options['modelsPath'],
			'migrations_path'     =>  $this->_options['migrationsPath'],
			'sql_path'            =>  $this->_options['sqlPath'],
			'yaml_schema_path'    =>  $this->_options['yamlSchemaPath']
		);
		
		return new Doctrine_Cli($config);
	}
}