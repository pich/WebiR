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
 * @package    App_R_DataSet
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Column.php 323 2010-04-19 07:49:54Z dbojdo $
 */

/**
 *
 * @property integer $id
 * @property integer $index
 * @property integer $data_set_id
 * @property string $label
 * @property string $type
 * @property boolean $is_ordered
 * @property App_R_Data_Set $data_set
 * @property string $description
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class App_R_DataSet_Column extends Doctrine_Record {
	const TYPE_FACTOR = 'factor';
	const TYPE_INTEGER = 'integer';
	const TYPE_NUMERIC = 'numeric';
	const TYPE_LOGICAL = 'logical';

	public function setTableDefinition() {
		$this->setTableName('r_data_set_column');

		$this->hasColumn('id','integer',4,array('primary'=>true,'autoincrement'=>true));
		$this->hasColumn('data_set_id','integer',4,array('notnull'=>true));
		$this->hasColumn('index','string',null,array('notnull'=>true));
		$this->hasColumn('label','string',null,array('notnull'=>true));
		$this->hasColumn('label_short','string',null,array('notnull'=>false));
		$this->hasColumn('type','string',16,array('notnull'=>false));
		$this->hasColumn('description','string',256,array('notnull'=>false));
		$this->hasColumn('is_ordered','boolean',null,array('notnull'=>false));
		$this->hasColumn('segment_id','integer',4,array('notnull'=>false));

		$this->actAs('Sortable',array('manyListsColumn'=>'data_set_id'));
	}

	public function setUp() {
		$this->hasOne('App_R_DataSet as data_set',array('local'=>'data_set_id','foreign'=>'id','onDelete'=>'CASCADE', 'onUpdate' => 'CASCADE'));
		$this->index('fki_r_data_set_column_data_set_id_fkey', array('fields' => array('data_set_id')));
		$this->hasMany('App_R_DataSet_ColumnLevel as levels',array('local'=>'id','foreign'=>'column_id'));
		$this->hasOne('App_R_DataSet_Segment as segment',array('local'=>'segment_id','foreign'=>'id','onDelete'=>'SET NULL'));
		$this->index('fki_r_data_set_column_segment_id_fkey', array('fields' => array('segment_id')));
	}

	public function isQuantitative() {
		return in_array($this->type,array(App_R_DataSet_Column::TYPE_INTEGER,App_R_DataSet_Column::TYPE_NUMERIC));
	}

	public function isOrdered() {
		return $this->is_ordered == true ? true : false;
	}

	public function changeType($type) {
		$wSettings = Zend_Registry::get('webir');

		$type = mb_strtolower($type);
		$v = new Zend_Validate_InArray(array(self::TYPE_FACTOR,self::TYPE_LOGICAL,self::TYPE_NUMERIC,self::TYPE_INTEGER));
		if(!$v->isValid($type)) {
			throw new Webir_Exception('Niepoprawny typ zmiennej: '. $type);
		}

		$oFun = App_R_Function::factory('as'.ucfirst($type),array('data_set'=>$this->data_set,'variables'=>array($this),'mode'=>'advance'));
		$oFun->runTask();

		$result = $oFun->getResult();

		if($this->type !== $type) {
			$this->is_ordered = $type == self::TYPE_FACTOR ? false : null;
		}
		$this->type = $type;
		$this->save();

		return $result;
	}

	public function getLevelsR() {
		$oFun = App_R_Function::factory(($this->type == 'factor' ? 'levels' : 'unique'),array('data_set'=>$this->data_set,'variables'=>array($this),'mode'=>'advance'));
		$oFun->runTask();
		$result = $oFun->getResult();
		$oFun->getTask()->delete();

		return $result;
	}

	public function levelToNa($level) {
		$oFun = App_R_Function::factory('na',array('data_set'=>$this->data_set,'variables'=>array($this),'mode'=>'advance'));
			$oFun->setParam('level',(string)$level);
			$oFun->runTask();
		$result = $oFun->getResult();
		$oFun->getTask()->delete();
		if($result->success == true) {
			Doctrine_Query::create()->delete('App_R_DataSet_ColumnLevel')->where('value = ?',$level)->addWhere('column_id = ?',$this->id)->execute();
		}

		return $result;
	}

	public function orderLevels($arLevels,$ordered) {
		$oFun = App_R_Function::factory('order',array('variables'=>array($this),'data_set'=>$this->data_set,'mode'=>'advance'));
			$oFun->setParam('levels',$arLevels);
			$oFun->setParam('ordered',$ordered);
			$oFun->runTask();
		$result = $oFun->getResult();
		$oFun->getTask()->delete();
		if($result->success == true) {
			$this->is_ordered = $ordered;
			$this->save();
		}

		return $result;
	}
}