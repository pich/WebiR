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
 * @version    $Id: R.php 291 2010-04-15 13:36:00Z dbojdo $
 */

/**
 * R Project resources.
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Application_Resource_R extends Zend_Application_Resource_ResourceAbstract {
	
	public function init() {
		// Return resource so bootstrap will store it in the registry
		return $this->getR();
	}

	public function getR() {		
		// Pass in this resource 
		Webir_R::injectResource($this);

		$key = isset($this->_options['settingsRegistryKey']) ? $this->_options['settingsRegistryKey'] : 'webir';
		$settings = isset($this->_options['settings']) ? $this->_options['settings'] : array();
		Zend_Registry::set($key,$settings);
		
		// usawiamy bcscale
		if(isset($settings['bcscale'])) {bcscale($settings['bcscale']);}
		
		return $this;
	}
	
}