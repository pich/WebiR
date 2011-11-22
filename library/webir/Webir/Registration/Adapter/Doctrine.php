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
 * @package    Webir_Registration_Adapter
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Doctrine.php 89 2010-04-01 14:28:38Z argasek $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Registration_Adapter_Doctrine extends Webir_Configurable_Abstract implements Webir_Registration_Adapter_Interface {

	protected $options = array('identityClass'=>'User'
														,'identityProperty'=>'username'
														,'credentialProperty'=>'password');

	protected $_identity;

	/**
	 * (non-PHPdoc)
	 * @see library/webir/Webir/Registration/Adapter/Webir_Registration_Adapter_Interface#register($request)
	 */
	public function register(Zend_Controller_Request_Abstract $request) {
		$email = filter_var($request->email, FILTER_SANITIZE_EMAIL);
		$password = trim($request->password);
		$passwordConfirm = trim($request->{'password-confirm'});
		$request->setParam('email',$email);


		$user = Doctrine::getTable($this->option('identityClass'))->{'findOneBy' . $this->option('identityProperty')}($email);
		$resultClass = $this->option('resultClass');

		if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
			return new $resultClass(constant($resultClass.'::FAILURE_IDENTITY_INVALID'),null,array('Incorrect e-mail address'));
		}

		if($user) {
			return new $resultClass(constant($resultClass.'::FAILURE_IDENTITY_IN_USE'),null,array('This e-mail address has been used'));
		}

		if($password == $passwordConfirm) {
			if(mb_strlen($password) < $this->option('passwordLength')) {
				return new $resultClass(constant($resultClass.'::FAILURE_CREDENTIAL_INVALID'),null,array(sprintf('Your password is too short (at least %s characters)',$this->option('passwordLength'))));
			}
		} else {
			return new $resultClass(constant($resultClass.'::FAILURE_CREDENTIAL_CONFIRM_FAILS'),null,array('Password confirm fails'));
		}

		// recaptcha
		if ($this->hasOption('recaptcha')) {
			$rOptions = $this->option('recaptcha');
			// Activate recaptcha only if set so.
			if ($rOptions['active'] == 1) {
				$recaptcha = new Zend_Service_Recaptcha($rOptions['publicKey'],$rOptions['privateKey']);
				if(empty($request->recaptcha_response_field) || !$recaptcha->verify($request->recaptcha_challenge_field,$request->recaptcha_response_field)) {
					return new $resultClass(constant($resultClass.'::FAILURE_RECAPTCHA_FAILS'),null,array('Invalid reCAPTCHA phrase'));
				}
			}
		}

		$user = new App_User();
		$user->email = $email;
		$saltShakerCls = $this->option('saltShaker');
		$saltShaker = new $saltShakerCls;
		$user->setPassword($password, $saltShaker, $this->option('salt'));
		$user->role_id = $this->option('defaultRoleId');
		$user->save();

		$this->_identity = $user;
		$this->_sendActivationMail();

		return new $resultClass(constant($resultClass.'::ACTIVATION_NEED'),$user,array('Registration successful, but activation is need'));
	}

	/**
	 * (non-PHPdoc)
	 * @see library/webir/Webir/Registration/Adapter/Webir_Registration_Adapter_Interface#activate($identity)
	 */
	public function activate($hash) {
		$resultClass = $this->option('resultClass');

		$identity = Doctrine_Query::create()->select()->from($this->option('identityClass'))->where('MD5(CONCAT(?,email)) = ?',array($this->option('salt'),$hash))->addWhere('log_num IS NULL')->fetchOne();

		if(!$identity) {
			return new $resultClass(constant($resultClass.'::FAILURE_IDENTITY_NOT_FOUND'),$identity,array('Activation fails. Identity not found.'));
		}

		if(!$identity->log_num !== null) {
			$identity->log_num = 0;
			$identity->save();
		}

		return new $resultClass(constant($resultClass.'::ACTIVATION_SUCCESS'),$identity,array('Activation successful'));
	}

	/**
	 * Send activation e-mail
	 * @return void
	 */
	protected function _sendActivationMail() {
		$view = new Zend_View();
		$view->setBasePath('../application/views/mail');
		$view->hash = $this->_getHash();

		$message = new Zend_Mail('UTF-8');
		$message->addTo($this->_identity->email);
		$message->setSubject('WebiR - Aktywacja konta');
		$message->setBodyText($view->render('activation_plain.phtml'));
		$message->setBodyHtml($view->render('activation_html.phtml'));
		$message->send();
	}

	/**
	 * (non-PHPdoc)
	 * @see library/webir/Webir/Registration/Adapter/Webir_Registration_Adapter_Interface#getHash($identity)
	 */
	protected function _getHash() {
		if(!isset($this->_identity)) {
			throw new Webir_Exception('Nie zarejestrowano użytkownika');
		}

		return md5($this->option('salt') . $this->_identity->email);
	}
}