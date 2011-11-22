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
 * @version    $Id: SegmentRestricted.php 262 2010-04-14 12:24:25Z argasek $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class App_R_DataSet_SegmentRestricted extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('r_data_set_column_segment_restricted');

		$this->hasColumn('compare_id','integer',4,array('primary'=>true));
		$this->hasColumn('restricted_id','integer',4,array('primary'=>true));

		$this->actAs('Sortable',array('manyListsColumn'=>'data_set_id'));

		$this->hasOne('App_R_DataSet_Segment as compare_id',array('local'=>'compare_id','foreing'=>'id','onDelete'=>'CASCADE','onUpdate'=>'CASCADE'));
		$this->index('fki_r_data_set_column_segment_restricted_compare_id_fkey',array('fields' => array('compare_id')));
		$this->hasOne('App_R_DataSet_Segment as restricted_id',array('local'=>'restricted_id','foreign'=>'id','onDelete'=>'CASCADE','onUpdate'=>'CASCADE'));
		$this->index('fki_r_data_set_column_segment_restricted_restricted_id_fkey',array('fields' => array('restricted_id')));
	}
}