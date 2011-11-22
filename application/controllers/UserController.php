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
 * @package    Controller
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: UserController.php 236 2010-04-12 16:50:20Z dbojdo $
 */

/**
 * User controller class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class UserController extends Webir_Controller_Subpage {

	/**
	 * @desc Logowanie
	 * @return bool
	 */
	public function signinAction() {
		// This action either redirects to the /welcome or forwards to /.
		// In either case we clear JS & CSS provided by Webir_Controller_Subpage
		$this->view->headLink()->setStylesheet('');
		$this->view->headScript()->setFile('');
		// Set the title
		$this->view->headTitle('Logowanie do serwisu');
		/**
		 * @var Webir_Application_Resource_User
		 */
		$userResource = Zend_Registry::get('user');

		$email = filter_var($this->getRequest()->email, FILTER_SANITIZE_EMAIL);
		$password = $this->getRequest()->password;

		// Try to authenticate user using supplied credentials
		$adapter = $userResource->getAuthAdapter();
		$adapter->setUsername($email);
		$adapter->setCredential($password);
		/**
		 *
		 * @var Zend_Auth_Result $result
		 */
		$result = $userResource->getAuth()->authenticate($adapter);
		// If authentication succeeded, redirect to /welcome. If not, display error in index.
		if ($result->getCode() == Webir_Registration_Result::SUCCESS) {
			if ($this->getRequest()->persistent == 1) {
				// Calculate a year from now on in seconds
				$yearLater = new Zend_Date();
				$yearLater->addYear(1);
				$sessionLengthSeconds = $yearLater->sub(new Zend_Date())->toValue();
				// @todo Set the session length for a year from now on, but should be configurable
				Zend_Session::rememberMe($sessionLengthSeconds);
			}
			$userResource->getAuth()->getIdentity()->login();
			$this->_redirect($this->view->url(array(), 'welcome'));
		} else {
			$this->view->result = $result;
			$this->_forward('index', 'index', 'default');
		}
		return true;
	}

	/**
	 * @desc Rejestracja
	 * @return void
	 */
	public function signupAction() {
		$this->view->headTitle('Rejestracja konta');
		$values = array('email'=>'','password'=>'','password-confirm'=>'');

		$userResource = Zend_Registry::get('user');
		$options = $userResource->getOptions();

		$adapterOptions = $options['registration']['adapter']['options'];
		$useRecaptcha = (isset($adapterOptions['recaptcha']) && $adapterOptions['recaptcha']['active'] == 1 ? true : false);
		$this->view->recaptcha = false;

		if ($useRecaptcha === true) {
			$optionsRecaptcha = $adapterOptions['recaptcha'];

			$recaptcha = new Webir_Service_ReCaptcha(
				$optionsRecaptcha['publicKey'],
				$optionsRecaptcha['privateKey']
			);
		}

		if ($this->getRequest()->isPost()) {
			$registrationAdapter = $userResource->getRegistrationAdapter();
			$this->view->result = $registrationAdapter->register($this->getRequest());
		}

		$values = array_merge($values,$this->getRequest()->getParams());

		$this->view->values = $values;
		if ($useRecaptcha === true) {
			$this->view->recaptcha = $recaptcha;
		}
	}

	/**
	 * @desc Aktywacja
	 * @return void
	 */
	public function activationAction() {
		$userResource = Zend_Registry::get('user');
		$registrationAdapter = $userResource->getRegistrationAdapter();
		$result = $registrationAdapter->activate($this->getRequest()->hash);

		if($result->getCode() < 3) {
			// loguje użytkownika i przerzucam na strone główną
			$userResource->getAuth()->getStorage()->write($result->getIdentity());
			$this->view->headMeta()->appendHttpEquiv('Refresh',sprintf('%d;URL=%s',5,$this->view->url(array(),'welcome')));
		}

		$this->view->result = $result;
	}

	/**
	 * @desc Wylogowanie
	 * @return void
	 */
	public function signoutAction() {
		$userResource = Zend_Registry::get('user');
		if ($userResource->getAuth()->hasIdentity()) {
			$userResource->getAuth()->clearIdentity();
			Zend_Session::forgetMe();
		}

		$this->_redirect($this->view->url(array(), 'index'));
	}

	/**
	 * User account settings
	 *
	 * @desc Konto użytkownika
	 */
	public function accountAction() {

	}
}