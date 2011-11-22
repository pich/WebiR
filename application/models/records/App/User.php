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
 * @category   App
 * @package    User
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: User.php 302 2010-04-16 10:06:06Z argasek $
 */

/**
 * Basic user entity.
 *
 * @property integer $id ID
 * @property string $email E-mail address
 * @property string $password A salted hash of user password
 * @property integer $role_id ID of role bound to user
 * @property string $log_date Date and time when the user last authenticated him/herself successfully (null, if the user never logged in)
 * @property integer $log_num Number of times the user logged in (null, if the user never logged in)
 * @property bool $is_admin Is user account administrative
 * @property string $expires Date and time when the user account expires. If null, user account never expires.
 * @property bool $active Is user account active?
 * @property App_Acl_Role $role User role object
 * @property Doctrine_Collection $tasks User's tasks
 *
 * @todo Should the automatic authentication count too when increasing $log_num?
 * @todo Currently any modification to the record changes $modified, no matter if updated is triggered by user or administrator. Is it OK?
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_User extends Doctrine_Record implements Zend_Acl_Role_Interface {

	/**
	 * Setup record, table name etc.
	 */
	public function setTableDefinition() {
		$this->setTableName('users');

		$this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('email', 'string', 128, array('notnull' => true, 'unique' => true));
		$this->hasColumn('password', 'string', 64, array('notnull' => true));
		$this->hasColumn('role_id', 'integer', 1, array('notnull' => true));
		$this->hasColumn('log_date', 'timestamp', null, array('notnull' => false));
		$this->hasColumn('log_num', 'integer', 4, array('notnull' => false, 'default' => null));
		$this->hasColumn('is_admin','boolean',null,array('notnull'=>true,'default'=>'false'));
		$this->hasColumn('expires','date',null,array('notnull'=>false));
		$this->hasColumn('active', 'boolean', null, array('default' => true,'notnull'=>true));
	}

	/**
	 * Describe relationships.
	 */
	public function setUp() {
		parent::setUp();
		$this->hasOne('App_Acl_Role as role', array('local' => 'role_id', 'foreign' => 'role_id', 'onUpdate' => 'CASCADE'));
		$this->index('fki_users_role_id_fkey', array('fields' => array('role_id')));
		$this->hasMany('App_R_Task as tasks',array('local'=>'id','foreign'=>'user_id'));
	}

	/**
	 * Implementation of Zend_Acl_Role_Interface.
	 *
	 * @see Zend_Acl_Role_Interface
	 * @return string Unique role identifier
	 */
	public function getRoleId() {
		return $this->role->name;
	}

	public function login() {
		$this->log_date = new Doctrine_Expression('atTimeZone(UTC)');
		$this->log_num++;
		$this->save();
	}

	/**
	 * Checks if this user is admin uset
	 * @return boolean
	 */
	public function isAdmin() {
		return $this->is_admin;
	}

	/**
	 * Ustawia hasło
	 * @param string $password
	 * @param Webir_Auth_SaltShaker_Interface $saltShaker
	 * @param string $salt
	 * @return void
	 */
	public function setPassword($password,Webir_Auth_SaltShaker_Interface $saltShaker, $salt = null) {
		$this->password = $saltShaker->encode($password, $salt);
	}

	/**
	 * Pre INSERT record event listener (fired when the record state is TDIRTY).
	 *
	 * @param Doctrine_Event $event
	 */
	public function preInsert($event) {
//		$this->created = new Zend_Date();
	}

	/**
	 * Pre UPDATE record event listener (fired when the record state is DIRTY).
	 *
	 * @param Doctrine_Event $event
	 */
	public function preUpdate($event) {
//		$this->modified = new Zend_Date();
	}

}
