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
 * @package    App_Acl
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Access.php 290 2010-04-15 13:34:59Z argasek $
 */

/**
 *
 *
 * @property integer $id
 * @property string $action_id
 * @property integer $role_id
 * @property string $privilege
 * @property boolean $access
 * @property App_Acl_Role $role
 * @property App_Acl_Action $action
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl
 *
 */
class App_Acl_Access extends Doctrine_Record {
	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Record/Doctrine_Record_Abstract#setTableDefinition()
	 */
	public function setTableDefinition() {
		$this->setTableName('acl_access');

		$this->hasColumn('id','integer',4,array('primary'=>true,'autoincrement'=>true));
		$this->hasColumn('action_id','integer',4,array('notnull'=>true));
		$this->hasColumn('role_id','integer',4,array('notnull'=>true));
		$this->hasColumn('privilege','string',128,array('notnull'=>true));
		$this->hasColumn('is_allowed','boolean',null,array('notnull'=>true));
	}

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Doctrine_Record#setUp()
	 */
	public function setUp() {
		$this->unique(array('action_id','role_id','privilege'));

		$this->hasOne('App_Acl_Role as role',array('local'=>'role_id','foreign'=>'role_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		$this->index('fki_acl_access_role_id_fkey', array('fields' => array('role_id')));
		$this->hasOne('App_Acl_Action as action',array('local'=>'action_id','foreign'=>'action_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		$this->index('fki_acl_access_action_id_fkey', array('fields' => array('action_id')));
	}

	static public function isAllowed(App_Acl_Role $role,App_Acl_Action $resource,$privilege = 'grant') {
		$dql = Doctrine_Query::create()->select('COALESCE(a.is_allowed) as is_allowed')
																		->from('App_Acl_Access a')
																			->leftJoin('a.role r1')
																			->leftJoin('a.action r3')
																		->addWhere('a.role_id IN (SELECT r2.role_id FROM App_Acl_Role r2 WHERE r2.lft <= ? AND r2.rgt >= ? AND r2.root_id = ?)',array($role->lft,$role->rgt,$role->root_id))
																		->addWhere('a.action_id IN (SELECT r4.action_id FROM App_Acl_Action r4 WHERE r4.lft <= ? AND r4.rgt >= ? AND r4.root_id = ?)',array($resource->lft,$resource->rgt,$resource->root_id))
																		->addWhere('a.privilege = ? OR a.privilege = ?',array($privilege,'grant'))
																		->addOrderBy('r1.level DESC')->addOrderBy('r3.level DESC')->limit(1);

		$r = $dql->execute();
		return $r->count() > 0 ? $r->get(0)->is_allowed : false;
	}

	/**
	 *
	 * @return Webir_Acl_Role
	 */
	static public function getDefaultRole() {
		return Doctrine::getTable('App_Acl_Role')->find(0);
	}
}