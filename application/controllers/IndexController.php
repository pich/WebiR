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
 * @package    IndexController
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: IndexController.php 419 2011-11-16 10:58:44Z dbojdo $
 */

/**
 * Index controller class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class IndexController extends Webir_Controller {

	public function preDispatch() {
		parent::preDispatch();
		// Title page needs other CSS stylesheet than other static pages.
		if ($this->getRequest()->getActionName() === 'index') {
			$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/index.css'));
		} else {
			// $this->_helper->layout->setLayout('subpage');
			$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/subpage.css'));
			$this->view->headScript()->appendFile($this->view->baseUrl('js/subpage.js'));
		}
	}

	/**
	 * @desc Strona główna
	 */
	public function indexAction() {
 		if (Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect($this->view->url(array(),'welcome'));
			return true;
		}
		

		$this->view->headTitle('Łatwa analiza statystyczna dla każdego');
	}

	/**
	 * @desc O projekcie
	 */
	public function aboutAction() {
		$this->view->headTitle('O projekcie');
	}

	/**
	 * @desc Tour po projekcie
	 */
	public function tourAction() {
		$this->view->headTitle('Screencast: Poznaj WebiR');
	}

	/**
	 * @desc Kontak
	 */
	public function contactAction() {
		$this->view->headTitle('Kontakt');
	}

	/**
	 * @desc Open-source
	 */
	public function opensourceAction() {
		$this->view->headTitle('Otwarte oprogramowanie');
	}
}
