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
 * @package    Webir_Controller
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Subpage.php 396 2010-06-10 13:28:09Z argasek $
 */


/**
 * Standard subpage controller class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Controller_Subpage extends Webir_Controller {

	public function init() {
		parent::init();
	}

	public function preDispatch() {
		parent::preDispatch();
		// ExtJS
	//	$this->view->headLink()->prependStylesheet('http://extjs.cachefly.net/ext-3.1.1/resources/css/ext-all.css');
	//	$this->view->headScript()->prependFile('http://extjs.cachefly.net/ext-3.1.1/ext-all.js');
	//	$this->view->headScript()->prependFile('http://extjs.cachefly.net/ext-3.1.1/adapter/ext/ext-base.js');

		$this->view->headLink()->prependStylesheet('/js/ext/resources/css/xtheme-gray.css');
		$this->view->headLink()->prependStylesheet('/js/ext/resources/css/ext-all-notheme.css');
		//$this->view->headScript()->prependFile('/js/ext/examples/ux/Focus.js');
		$this->view->headScript()->prependFile('/js/ext/src/locale/ext-lang-pl.js');
		$this->view->headScript()->prependFile('/js/ext/ext-all-debug.js');
		$this->view->headScript()->prependFile('/js/ext/adapter/ext/ext-base.js');

		// Subpage scripts
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/subpage.css'), 'all');
		$this->view->headScript()->appendFile($this->view->baseUrl('js/subpage.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('js/Webir/Common.js'));
	}

	public function postDispatch() {
		// This needs to be associated in postDispatch() because user might have triggered an action
		// changing the number of unseen processes.
		$this->view->userProcessCount = $this->_user ? App_R_Task::getNumberOfUnseenTasks($this->_user->id) : 0;
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/print.css'), 'print');
	}

}