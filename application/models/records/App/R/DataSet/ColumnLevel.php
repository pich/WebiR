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
 * @version    $Id: ColumnLevel.php 262 2010-04-14 12:24:25Z argasek $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class App_R_DataSet_ColumnLevel extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('r_data_set_column_level');

		$this->hasColumn('id','integer',4,array('primary'=>true,'autoincrement'=>true));
		$this->hasColumn('column_id','integer',4,array('notnull'=>true));
		$this->hasColumn('value','string',null,array('notnull'=>true));
		$this->actAs('Sortable',array('manyListsColumn'=>'column_id'));
	}

	public function setUp() {
		$this->hasOne('App_R_DataSet_Column as column',array('local'=>'column_id','foreign'=>'id','onDelete'=>'CASCADE', 'onUpdate' => 'CASCADE'));
		$this->index('fki_r_data_set_column_level_column_id_fkey', array('fields' => array('column_id')));
	}
}