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
 * @package    Acl
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Role.php 290 2010-04-15 13:34:59Z argasek $
 */

/**
 * Basic ACL user role.
 *
 * @property integer $role_id
 * @property string $name
 * @property string $description
 * @property integer $lft Left value
 * @property integer $rgt Right value
 * @property integer $level Nesting level in a tree
 * @property integer $root_id Root ID of tree
 * @method Doctrine_Node_NestedSet getNode()
 *
 * @todo Implementation needed
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class App_Acl_Role extends Doctrine_Record implements Zend_Acl_Role_Interface {

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Record/Doctrine_Record_Abstract#setTableDefinition()
	 */
	public function setTableDefinition() {
		$this->setTableName('acl_role');

		$this->hasColumn('role_id','integer',4,array('primary'=>true,'autoincrement'=>true));
		$this->hasColumn('name','string',128,array('notnull'=>true));
		$this->hasColumn('description','string',128,array('notnull'=>true));
	}

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Doctrine_Record#setUp()
	 */
	public function setUp() {
		$this->actAs('NestedSet',array(
			'hasManyRoots'     => true,
			'rootColumnName'   => 'root_id'
		));

		$this->hasOne('App_Acl_Role as root', array('local' => 'root_id', 'foreign' => 'role_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		$this->index('fki_acl_role_root_id_fkey', array('fields' => array('root_id')));

	}

	/**
	 * (non-PHPdoc)
	 * @see library/zend/Zend/Acl/Role/Zend_Acl_Role_Interface#getRoleId()
	 */
	public function getRoleId() {
		return $this->role_id;
	}

	/**
	 *
	 * @param Webir_Acl_Action $resource
	 * @param string $privilege
	 * @return boolean
	 */
	public function isAllowed(App_Acl_Action $action,$privilege = 'grant') {
		return App_Acl_Access::isAllowed($this,$action,$privilege);
	}
}