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
 * @version    $Id: JavaScriptController.php 396 2010-06-10 13:28:09Z argasek $
 */

/**
 *
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class JavaScriptController extends Webir_Controller {
	/**
	 * @desc Pobieranie danych o użytkowniku i wybranym zestawie danych
	 * @return void
	 */
	public function commonAction() {
		$arUser['id'] = $this->_user->id;
		$arUser['email'] = $this->_user->email;
		$arUser['is_admin'] = $this->_user->is_admin;
		$arUser['role_id'] = $this->_user->role_id;
		$arUser['admin_mode'] = $this->_session->adminMode;
		$arUser['accessibility'] = true;
		$this->view->user = $arUser;
	}

	/**
	 * @desc Pobieranie informacji o wybranych zmiennych
	 * @return void
	 */
	public function webirAnalysisChooseVariablesAction() {
		$this->view->data_set_id = $this->_session->analysis_data_set->id;
		$this->view->variables = $this->_session->analysis_variables->getKeys();
		$arSubsets = array();
		foreach($this->_session->analysis_subsets as $subset) {
			$arSubset['value'] = $subset->toArray();
			$arSubset['variable'] = $subset->column->toArray();
			$arSubsets[] = $arSubset;
		}
		$this->view->subsets = $arSubsets;

	}

	/**
	 * @desc Pobieranie danych dla datasetu
	 * @return unknown_type
	 */
	public function webirDatasetIndexAction() {
		$wSettings = Zend_Registry::get('webir');
		foreach($wSettings['encoding'] as $value=>$label) {
			$arEncoding[] = array('value'=>$value,'label'=>$label);
		}
		$this->view->arStatus = array('Oczekuje na przetworzenie','Przetwarzanie','Gotowy','Nieużywalny');
		$this->view->arEncoding = $arEncoding;
		$this->view->defaultEncoding = $wSettings['defaultDataSetEncoding'];
	}
	
	/**
	 * @desc Pobieranie danych dla anallizy advance
	 * @return unknown_type
	 */
	public function advancedCommonAction() {
		$staticVars = array();
		$staticVars['data_set_id'] = isset($this->_session->analysis_data_set->id) ? $this->_session->analysis_data_set->id : 0;
		$this->view->static = $staticVars;
	}

	public function postDispatch() {
		parent::postDispatch();
		$this->getHelper('layout')->disableLayout();
		$this->_response->setHeader('Content-Type','application/javascript; charset=UTF-8',true);
	}
}