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
 * @package    Webir_Captcha
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Controller.php 392 2010-06-09 11:37:27Z argasek $
 */


/**
 * Standard action controller class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
abstract class Webir_Controller extends Zend_Controller_Action {
	/**
	 * HTTP content type
	 * @var string
	 */
	private $_contentType;

	/**
	 * HTTP output charset
	 * @var string
	 */
	private $_contentOutputCharset;

	/**
	 *
	 * @var Zend_Session_Namespace
	 */
	protected $_session;

	/**
	 * @var App_User
	 */
	protected $_user;

	public function init() {
		parent::init();
		$this->_session = new Zend_Session_Namespace();
		$this->_user = Zend_Registry::get('user')->getAuth()->getIdentity();
		// At the moment, we use these values as default
		$this->_contentType = 'text/html';
		$this->_contentStyleType = 'text/css';
		// TODO: A bit better than a previous version, because this is set in application.ini, but still, it probably should be set as the independent resource variable
		$this->_contentOutputCharset = mb_http_output();
		// Set the default output type and charset
		$this->getResponse()->setHeader('Content-Type', $this->_contentType.'; charset=' . $this->_contentOutputCharset);		
		$this->getResponse()->setHeader('Content-Style-Type', $this->_contentStyleType);
	}

	public function preDispatch() {
		parent::preDispatch();
		// Set common meta tags
		$this->view->headMeta()->appendHttpEquiv('imagetoolbar', 'no');
		$this->view->headMeta()->appendHttpEquiv('Content-Type', $this->_contentType.'; charset=' . $this->_contentOutputCharset);
		$this->view->headMeta()->appendHttpEquiv('Content-Style-Type', $this->_contentStyleType);
		// Set common CSS styles
		//$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/reset.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/base.css'), 'all');
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/common.css'), 'all');
		// FamFamFam Silk Icons (sprite version)
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('css/fam-sprite.css'));
		// Set common JavaScripts to load (libraries)
		$this->view->headScript()->appendFile($this->view->baseUrl('js/jquery-1.4.2.min.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('js/jquery.corner-2.09.js'));
		// Set common JavaScripts to load (actual code)
		$this->view->headScript()->appendFile($this->view->baseUrl('js/common.js'));

		$this->view->data_set = App_R_DataSet::getDefault();
	}

	public function postDispatch() {
		parent::postDispatch();
	}

}
