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
 * @version    $Id: Action.php 278 2010-04-15 09:21:23Z argasek $
 */

/**
 *
 * @property string $action_id ID (primary key) of the action
 * @property string $description Action's description (human readable)
 * @property integer $rgt ID of node on the right
 * @property integer $lft ID of node on the left
 * @property integer $level How deep a current node is nested in the tree
 * @property integer $root_id ID of tree's root (there can be many roots!)
 * @property Doctrine_Collection $access
 * @method Doctrine_Node_Nested_Set getNode()
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_Acl_Action extends Doctrine_Record implements Zend_Acl_Resource_Interface {

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Record/Doctrine_Record_Abstract#setTableDefinition()
	 */
	public function setTableDefinition() {
		$this->setTableName('acl_action');

		$this->hasColumn('action_id', 'integer', 4, array('primary' => true,'autoincrement' => true));
		$this->hasColumn('name', 'string', null, array('notnull' => true, 'unique' => true));
		$this->hasColumn('description', 'string', 256, array('notnull' => true, 'default' => ''));

		$this->actAs('NestedSet', array(
			'hasManyRoots'		=> true,
			'rootColumnName'	=> 'root_id'
		));

    $this->actAs('SoftDelete');
	}

	/**
	 * (non-PHPdoc)
	 * @see library/doctrine/Doctrine/Doctrine_Record#setUp()
	 */
	public function setUp() {
		$this->hasMany('App_Acl_Access as access',array('local'=>'id','foreign'=>'action_id'));
	}

	/**
	 * (non-PHPdoc)
	 * @see library/zend/Zend/Acl/Resource/Zend_Acl_Resource_Interface#getResourceId()
	 */
	public function getResourceId() {
		return $this->action_id;
	}

	/**
	 * Synchronize db with controller's actions
	 * @param Array $map
	 * @return void
	 */
	static function actionsToResources($map) {
		Doctrine::getTable('App_Acl_Action')->findAll()->delete();
		//set_include_path(':'.get_include_path());

		$resources = array();
		foreach($map as $moduleName => $controllers) {
			$resourceModule = array('id'=>'/'.$moduleName,'description'=>ucfirst($moduleName) .' Module','children'=>array());
			foreach($controllers as $controller) {
				$controllerName = ucfirst($controller) . 'Controller';
				require_once('../application/controllers/'.$controllerName.'.php');
				$controllerClass = new ReflectionClass($controllerName);
				$controllerComment = $controllerClass->getDocComment();
				preg_match('/@desc\s(.*)\n/',$controllerComment,$matches);
				$controllerDescription = count($matches) > 0 ? $matches[1] : null;
				$resourceController = array('id'=>'/'.implode('/',array($moduleName,$controller)),'description'=>$controllerDescription,'children'=>array());

				$methods = $controllerClass->getMethods(ReflectionMethod::IS_PUBLIC);
				foreach($methods as $method) {
					if(preg_match('/Action$/',$method->getName())) {
						$actionName = preg_replace('/Action$/','',$method->getName());
						$resourceName = '/' . implode('/',array($moduleName,$controller,$actionName));
						$comment = $method->getDocComment();
						preg_match('/@desc\s(.*)\n/',$comment,$matches);
						$resourceDescription = count($matches) > 0 ? $matches[1] : null;
						$resourceController['children'][] = array('id'=>$resourceName,'description'=>$resourceDescription,'children'=>array());
					}
				}

				$resourceModule['children'][] = $resourceController;
			}
			$resources[] = $resourceModule;
		}

		foreach($resources as $resource) {
			self::insertResource($resource);
		}
	}

	/**
	 * Inserts action to db
	 * @param unknown_type $arResource
	 * @param unknown_type $parent
	 * @return unknown_type
	 */
	static private function insertResource($arResource,$parent = null) {
		$resource = Doctrine::getTable('App_Acl_Action')->findOneByname($arResource['id']);
		$resource = !$resource ? new App_Acl_Action : $resource;
		$resource->name = $arResource['id'];
		$resource->description = $arResource['description'];
		$resource->deleted_at = null;

		if($resource->exists()) {
			if($parent === null) {
				$resource->save();
			} else {
				$resource->getNode()->moveAsFirstChildOf($parent);
			}
		} else {
			if($parent === null) {
				$resource->save();
				Doctrine::getTable('App_Acl_Action')->getTree()->createRoot($resource);
			} else {
				$resource->getNode()->insertAsLastChildOf($parent);
			}
		}

		foreach($arResource['children'] as $child) {
			self::insertResource($child,$resource);
		}
	}
}