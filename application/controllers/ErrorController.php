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
 * @version    $Id: ErrorController.php 387 2010-06-07 14:50:11Z argasek $
 */


/**
 * Error controller class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class ErrorController extends Webir_Controller_Subpage {

	/**
	 * @desc Błędy: Wyrzucanie wyjątków
	 * @return void
	 */
	public function errorAction() {
		$errors = $this->_getParam('error_handler');

		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$this->view->message = 'Page not found';
				break;
			default:
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$this->view->message = 'Application error';
				break;
		}
		
		$this->view->exception = $errors->exception;
		
		if($this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('asXmlHttpRequest') == 'true') {
			$this->_ajaxError($errors);
		}
		
		Webir_Debug::getInstance()->error->err($errors->exception);
	}
	
	private function _ajaxError($errors) {
		// TODO: brzydkie jak nie powiec co, jak będzie czas, to zrobie to ładnie :P
		Zend_Layout::getMvcInstance()->disableLayout();
		$json = new Webir_Json();
		$json->setError($errors->exception->getMessage() . $errors->exception->getTraceAsString());
		$this->view->json($json);
		$this->_response->setHeader('Content-Type','application/json',true);
		
		echo Zend_Json::encode($json);
		die();
	}
	
	/**
	 * @desc Błędy: Dostęp zabroniony
	 * @return void
	 */
	public function forbiddenAction() {
		throw new Webir_Exception(sprintf("You don't have permission to access %s of this application.", $this->getRequest()->getUserParam('forbiddenAction')));
	}
	
	/**
	 * @desc Błędy: Akcja domyślna
	 */
	public function defaultAction() {
		
	}

}