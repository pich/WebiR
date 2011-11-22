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
 * @version    $Id: Json.php 251 2010-04-13 10:16:42Z dbojdo $
 */

/**
 * Controller for handling JSON responses
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Controller_Json extends Zend_Controller_Action {
	/**
	 * @var Webir_Json
	 */
	protected $_json;
	
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
	
	public function init() {
		parent::init();
		
		$this->_contentType = 'application/json';
		$this->_contentOutputCharset = mb_http_output();
		$this->_json = new Webir_Json();
	}
	
	public function postDispatch() {
		parent::postDispatch();
		
		if($this->_json->getData() == null) {
			$this->_json->setData(array());
		}
	
		$this->getHelper('layout')->disableLayout();
		if ($this->getRequest()->isXmlHttpRequest()) {
			$this->getHelper('viewRenderer')->setNoRender();
			$this->getResponse()->setBody($this->_json->toJson());
		} else {
			$this->_contentType = 'text/plain';
			$this->view->json = Webir_Util_Text::jsonPrettyPrint($this->_json->toJson());
			$this->_helper->viewRenderer('ajax-response-as-html');			
		}
		$this->getResponse()->setHeader('Content-Type', $this->_contentType.'; charset=' . $this->_contentOutputCharset);
	}
}