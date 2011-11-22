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
 * @package    Webir_ExtJS
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Store.php 257 2010-04-13 21:58:17Z dbojdo $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
abstract class Webir_ExtJS_Store extends Webir_Configurable_Abstract {
	/**
	 * @var Zend_Controller_Request_Abstract
	 */
	protected $_request;
	
	/**
	 * @var Array
	 */
	protected $_options = array('sortFieldParam'=>'sort','sortDirParam'=>'dir','groupFieldParam'=>'groupBy','groupDirParam'=>'groupDir'
											,'limitParam'=>'limit','offsetParam'=>'start','aliases'=>true,'searchPhraseParam'=>'query','searchFieldsParam'=>'fields'
											,'defaultSortDir'=>'ASC','defaultGroupDir'=>'ASC','searchFields'=>array());
	
	const SORT_ASC = 'ASC';
	const SORT_DESC = 'DESC';

	/**
	 * @var Array
	 */
	protected $_params = array();
	
	public function __construct(Array $options = array(),Zend_Controller_Request_Abstract $request) {
		parent::__construct($options); 
		$this->_request = $request;
		$this->setDefinition();
	}
	
	abstract public function setDefinition();
	
	protected function _alias($field) {
		return $this->option('aliases') ? preg_replace('/_/','.',$field,1) : $field;
	}
	
	public function setBaseQuery(Doctrine_Query $dql) {
		$this->_dql = $dql;
	}
	
	protected function getParam($param,$default=null) {
		return $this->_request->getParam($param,$default);
	}
	
	public function filter($param) {
		$f = new Zend_Filter();
		$f->addFilter(new Zend_Filter_StringTrim());
		$f->addFilter(new Zend_Filter_StripTags());
		switch($param) {
			case $this->option('sortFieldParam'):
			case $this->option('groupFieldParam'):
				if($this->option('aliases')) {
					$f->addFilter(new Webir_Filter_PregReplace(array('match'=>'/_/','replace'=>'.','limit'=>1)));
				}
			break;
			case $this->option('sortDirParam'):
			case $this->option('groupDirParam'):
				$f->addFilter(new Zend_Filter_StringToUpper());
				$f->addFilter(new Webir_Filter_InList(array(self::SORT_ASC,self::SORT_DESC)));
			break;
			case $this->option('limitParam'):
			case $this->option('offsetParam'):
				$f->addFilter(new Zend_Filter_Int());
			break;
			case $this->option('searchPhraseParam'):
				$f->addFilter(new Zend_Filter_StringToLower());
			break;
			case $this->option('searchFieldsParam'):
				$f->addFilter(new Webir_Filter_Json());
			break;
			default:
				
		}
		
		$f->addFilter(new Zend_Filter_Null());
		return $f->filter($this->getParam($param));
	}
	
	public function load($hydrationMode = Doctrine::HYDRATE_SCALAR) {
		// przeszukiwanie
		$phrase = $this->filter($this->option('searchPhraseParam'));
		$arFields = $this->filter($this->option('searchFieldsParam'));
		if(!empty($phrase)) {
			$arFields = empty($arFields) ? $this->option('searchFields') : array_intersect($arFields,$this->option('searchFields'));

			$f = new Zend_Filter();
				$f->addFilter(new Zend_Filter_StringTrim());
				$f->addFilter(new Zend_Filter_StripTags());
				if($this->option('aliases')) {
					$f->addFilter(new Webir_Filter_PregReplace(array('match'=>'/_/','replace'=>'.','limit'=>1)));
				}
				$f->addFilter(new Zend_Filter_Null());
						
			foreach($arFields as $key=>$field) {
				$arFields[$key] = $f->filter($field);
			}

			foreach($arFields as $field) {
				$q[] = sprintf('LOWER(%s) LIKE ?',$field);
				$par[] = '%'.mb_strtolower($phrase).'%';
			}

			if(isset($q)) {
				$this->_dql->addWhere(implode(' OR ',$q),$par);
			}
		}
		
		// sortowanie według grupy
		$groupField = $this->filter($this->option('groupFieldParam'));
		if(!empty($groupField)) {
			$this->_dql->addOrderBy($groupField . ' '.$this->filter($this->option('groupDirParam')));
		}
		
		// sortowanie
		$sortField = $this->filter($this->option('sortFieldParam'));
		if(!empty($sortField)) {
			$this->_dql->addOrderBy($sortField . ' '.$this->filter($this->option('sortDirParam')));
		}

		$total = $this->_dql->count();
		
		// limit
		$limit = $this->filter($this->option('limitParam'));
		if(!empty($limit)) {
			$this->_dql->limit($limit);
		}
		
		// offset
		$offset = $this->filter($this->option('offsetParam'));
		if(!empty($offset)) {
			$this->_dql->offset($offset);
		}
		
		$data = $this->_dql->execute(array(),$hydrationMode);
		
		return array('rows'=>$data,'total'=>$total);
	}
	
	public function setParam($key,$value) {
		$this->_params[$key] = $value;
	}
}