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
 * @version    $Id: User.php 381 2010-04-29 08:57:46Z dbojdo $
 */

/**
 * @todo Documentation!
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Application_Resource_User extends Zend_Application_Resource_ResourceAbstract {
	
	public function init() {
		if (isset($this->_options['acl']['enable']) && $this->_options['acl']['enable'] == 1) {
			$aclPluginOptions = array_merge($this->_options['common'], $this->_options['acl']);
			$aclPlugin = new $this->_options['acl']['pluginClass']($aclPluginOptions);
			Zend_Controller_Front::getInstance()->registerPlugin($aclPlugin);
		}
		Zend_Registry::set((isset($this->_options['registryKey']) ? $this->_options['registryKey'] : 'user'), $this);
		
		if(array_key_exists('defaultUser',$this->_options['common']) && !$this->getAuth()->hasIdentity()) {
			$adapter = $this->getAuthAdapter();
			$adapter->setUsername($this->_options['common']['defaultUser']['login']);
			$adapter->setCredential($this->_options['common']['defaultUser']['password']);
			$result = $this->getAuth()->authenticate($adapter);
		}
	}
	
	public function getAuth() {
		return call_user_func((isset($this->_options['auth']['class']) ? $this->_options['auth']['class'] : 'Zend_Auth') . '::getInstance');
	}
	
	/**
	 * 
	 * @return Zend_Auth_Adapter_Interface
	 */
	public function getAuthAdapter() {
		if(!isset($this->_options['auth']['adapter']['class'])) {
			throw new Webir_Exception('Adapter class not found');
		}

		$options = isset($this->_options['auth']['adapter']['options']) ? $this->_options['auth']['adapter']['options'] : array();
		$options = array_merge($this->_options['common'],$options);
		
		return new $this->_options['auth']['adapter']['class']($options);
	}
	
	/**
	 * 
	 * @return Webir_Registration_Interface
	 */
	public function getRegistrationAdapter() {
		if(!isset($this->_options['registration']['adapter']['class'])) {
			throw new Webir_Exception('Adapter class not found');
		}
		
		$options = isset($this->_options['registration']['adapter']['options']) ? $this->_options['registration']['adapter']['options'] : array();
		$options = array_merge($this->_options['common'],$options);
		
		return new $this->_options['registration']['adapter']['class']($options);
	}
}