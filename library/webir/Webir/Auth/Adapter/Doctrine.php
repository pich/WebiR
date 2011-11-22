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
 * @package    Webir_Controller_Plugin
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Doctrine.php 35 2010-03-23 09:26:08Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Auth_Adapter_Doctrine extends Webir_Configurable_Abstract implements Zend_Auth_Adapter_Interface {
	/**
	 * @var string
	 */
	protected $_username;
	
	/**
	 * @var string
	 */
	protected $_credential;
	
	protected $_options = array('credentialTreatment'=>'plain'
															,'identityClass'=>'User'
															,'resultClass'=>'Webir_Auth_Result'
															,'credentialProperty'=>'password'
															,'identityProperty'=>'username'
															,'validation'=>array('password','active','expires'));
	
	protected $_identity;
															
	protected $_result;
	
	/**
	 * Sets auth username
	 * @param string $username
	 * @return void
	 */
	public function setUsername($username) {
		$this->_username = $username;
	}
	
	/**
	 * Sets auth credential
	 * @param string $credential
	 * @return void
	 */
	public function setCredential($credential) {
		$this->_credential = $credential;
	}
	
	protected function _getUserObject() {
		return Doctrine::getTable($this->option('identityClass'))->{'findBy'.$this->option('identityProperty')}($this->_username);
	}
	
	protected function _validateResult($result) {
		$resultClass = $this->option('resultClass');

		switch(true) {
			case $result->count() == 0: 
				// user not found
				return new $resultClass(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,null,array(sprintf('User %s not found.',$this->_username)));
			break;
			case $result->count() > 1:
				// ambigouse
				return new $resultClass(Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS,null,array(sprintf('User %s is ambigous.',$this->_username)));
			break;	
		}
		
		$this->_identity = $result->get(0);
		
		foreach($this->option('validation') as $step) {
			$result = $this->{'_validation'.ucfirst($step)}();
			if($result instanceof $resultClass) {
				return $result;
			}
		}
		
		return new $resultClass(Zend_Auth_Result::SUCCESS,$this->_identity,array());
	}
	
	protected function _validationPassword() {
		$resultClass = $this->option('resultClass');
		if(!$this->_isPasswordCorrect($this->_identity->{$this->option('credentialProperty')})) {
			// password incorrect
			return new $resultClass(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,null,array(sprintf('Invalid password for user %s.',$this->_username)));
		}
		
		return true;
	}
	
	protected function _validationExpires() {
		$resultClass = $this->option('resultClass');
		if($this->hasOption('expiresProperty') && $this->_identity->{$this->option('expiresProperty')} !== null && Zend_Date::now()->compare($this->_identity->{$this->option('expiresProperty')},'yyyy-MM-dd HH:mm:dd') == 1) {
			// expires
			return new $resultClass(constant($this->option('resultClass').'::FAILURE_ACCOUNT_EXPIRES'),null,array(sprintf('Your account has expired %s. Contact system administrator.',$this->_identity->{$this->option('expiresProperty')})));
		}
		
		return true;
	}
	
	protected function _validationActive() {
		$resultClass = $this->option('resultClass');
		if($this->hasOption('activeProperty') && !$this->_identity->{$this->option('activeProperty')}) {
			return new $resultClass(constant($this->options('resultClass').'::FAILURE_ACCOUNT_DISABLED'),null,array(sprintf('Your account is disabled. Contact system administrator.')));
		}
		
		return true;
	}
	
	protected function _isPasswordCorrect($password) {
		if($this->hasOption('saltShaker')) {
			$cls = $this->option('saltShaker');
			$saltShaker = is_object($this->option('saltShaker')) ? $this->option('saltShaker') : new $cls;
			$salt = $this->hasOption('saltProperty') ? $this->_identity->{$this->option('saltProperty')} : ($this->hasOption('salt') ? $this->option('salt') : null);
			
			return $saltShaker->isValid($password,$this->_credential,$salt);
		} else {
			switch($this->option('credentialTreatment')) {
				case strtolower('md5'):
					$password = md5($password);
				break;
				case strtolower('sha1'):
					$password = sha1($password);
				break;
			}
			
			return $this->_credential == $password;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see library/zend/Zend/Auth/Adapter/Zend_Auth_Adapter_Interface#authenticate()
	 */
	public function authenticate() {
		$users = $this->_getUserObject();
		return $this->_validateResult($users);
	}
}