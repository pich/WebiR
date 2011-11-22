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
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Bootstrap.php 392 2010-06-09 11:37:27Z argasek $
 */

/**
 * Zend Application Bootstrap implementation
 * @uses       Zend_Application_Bootstrap_Bootstrap
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	/**
	 * Application first public release year
	 * 
	 * @var integer
	 */
	const COPYRIGHT_START_YEAR = 2010;
	
	/**
	 * Initialize the view
	 * 
	 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
	 * @return Zend_View
	 */
	protected function _initView() {
		// Initialize the default view
		$view = new Zend_View();
		$view->doctype('XHTML1_STRICT');
		$view->headTitle('WebiR: ');
		// Appending Favicon to all layouts and views
		$view->headLink(
			array(
				'rel' => 'shortcut icon',
				'type' =>'image/x-icon', 
				'href' => $view->baseUrl('img/favicon.ico'),
			),
			'PREPEND'
		);		
		
		// Appending Favicon iPhone OS
		$view->headLink(
			array(
				'rel' => 'apple-touch-icon',
				'type' =>'image/x-icon', 
				'href' => $view->baseUrl('img/iphone-icon.png'),
			),
			'PREPEND'
		);	
		
		$resourcesOptions = $this->getOption('resources');
		$lang = new Zend_Locale($resourcesOptions['locale']['default']);
		$view->lang = $lang->getLanguage();
		
		// Set the copyright year information
		$view->copyright = array();
		$view->copyright['start'] = (integer) self::COPYRIGHT_START_YEAR;
		$view->copyright['now'] = (integer) Zend_Date::now()->get('Y');
		
		// Load on demand and return the ViewRenderer helper, which automates the process
		// of setting the default view object in our controllers.
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view)->setViewSuffix('phtml');
		
		// We also register a JSON action helper; we are going to need it for AJAX JSON responses
		$jsonRenderer = new Zend_Controller_Action_Helper_Json();
		Zend_Controller_Action_HelperBroker::addHelper($jsonRenderer);
		
		// Return the view object, so it can be stored by the bootstrap
		return $view;
	}
	
}