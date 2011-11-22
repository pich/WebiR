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
 * @package    Controller
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: DatasetController.php 372 2010-04-21 09:40:38Z argasek $
 */

/**
 * WebiR project dataset management controller class.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class DatasetController extends Webir_Controller_Subpage {

	/**
	 * @desc Lista zbiorów danych użytkownika
	 */
	public function indexAction() {
		$this->view->headTitle('Twoje dane');
		$this->view->headScript()->appendFile('/js/ext/examples/ux/fileuploadfield/FileUploadField.js');
		$this->view->headScript()->appendFile('/js/ext-plugins/statictextfield/ext.ux.statictextfield.js');
		$this->view->headScript()->appendFile($this->view->baseUrl('js/Webir/DataSet.js'));

		$this->view->headScript()->appendFile($this->view->baseUrl('js/Webir/DataSet.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl($this->view->url(array('action'=>'webir-dataset-index'),'js')));
		$this->view->headLink()->appendStylesheet('/js/ext/examples/ux/fileuploadfield/css/fileuploadfield.css');
		$this->view->headLink()->appendStylesheet('/js/ext-plugins/statictextfield/ext.ux.statictextfield.css');
	}

	private function _getCsvReaderParams() {
		$webirSettings = Zend_Registry::get('webir');
		$reader_params = new stdClass;
		foreach($webirSettings['reader']['csv'] as $key => $value) {
			$reader_params->{$key} = $this->getRequest()->getParam($key,$value);
		}

		return $reader_params;
	}

	/**
	 * @desc Uploadowanie nowego zestawu danych
	 * @return void
	 */
	public function newAction() {
		if($this->getRequest()->getParam('asXmlHttpRequest') == 'true' && !$this->getRequest()->isPost()) {
			throw new Webir_Exception('Tą akcję można wywołąc tylko metodą POST');
		} elseif($this->getRequest()->isPost()) {
			$errors = array(); // błędy uploadu
			$wSettings = Zend_Registry::get('webir');
			Zend_Layout::getMvcInstance()->disableLayout();

			$f = new Zend_Filter();
				$f->addFilter(new Zend_Filter_StringTrim());
				$f->addFilter(new Zend_Filter_StripTags());

			// format pliku
			$format = $f->filter($this->getRequest()->format);
			$v = new Zend_Validate_InArray(array(App_R_DataSet::FORMAT_CSV,App_R_DataSet::FORMAT_RDATA));
			$format = $v->isValid($format) ? $f->filter($format) : App_R_DataSet::FORMAT_CSV;

			// kodowanie
			$v = new Zend_Validate();
				$v->addValidator(new Zend_Validate_NotEmpty());
				$v->addValidator(new Zend_Validate_InArray(array_keys($wSettings['encoding'])));
			$encoding = $f->filter($this->getRequest()->fileEncoding);
			if(!$v->isValid($encoding)) {
				$errors[] = array('id'=>'fileEncoding','msg'=>'Niepoprawne kodowanie znaków: '.$encoding);
			}

			$separator = $this->getRequest()->separator;
			$separator = $this->getRequest()->naString;

			// nazwa zbioru danych
			$v = new Zend_Validate();
				$v->addValidator(new Zend_Validate_NotEmpty());
				$v->addValidator(new Zend_Validate_StringLength(array('min'=>3,'max'=>128)));
			$name = $f->filter($this->getRequest()->name);
			if(!$v->isValid($name)) {
				$errors[] = array('id'=>'name','msg'=>'Niepoprawna nazwa zbioru danych (min. 3 znaki / maks. 128 znaków)');
			}

			// plik
			$upload = new Zend_File_Transfer_Adapter_Http();
			$upload->addValidator(new Zend_Validate_File_FilesSize(array('max'=>$wSettings['maxFileSize'],'min'=>1)));

			if(!$upload->isUploaded('dataset')) {
				$errors[] = array('id'=>'dataset','msg'=>'Nie wybrano pliku');
			} else {
				if(!$upload->isValid('dataset')) {
					$errors[] = array('id'=>'dataset','msg'=>'Niepoprawny rozmiar pliku (min. 1B / maks. 8MB)');
				}
			}

			if(empty($errors)) {
				$dataset = new App_R_DataSet_User();
				$dataset->format = $format;
				$dataset->name = $name;
				$dataset->user_id = $this->_user->id;
				$dataset->reader_params = $this->_getCsvReaderParams();

				$arInfo = $upload->getFileInfo('dataset');
				$dataset->source_filename = $arInfo['dataset']['name'];

				$upload->addFilter('rename', $dataset->filename, 'dataset');
				$upload->setDestination($wSettings['datasetsPath']);

				if(!$upload->receive('dataset')) {
					throw new Webir_Exception('Nie udało się wgrać pliku');
				} else {
					try {
						$dataset->save();
					} catch(Exception $e) {
						@unlink($wSettings['datasetsPath'] . DS . $dataset->filename);
						throw new Webir_Exception($e->getMessage());
					}
				}
			}
			echo Zend_Json::encode(array('success'=>empty($errors),'data'=>array(),'errors'=>$errors));
			die(); // brzydkie, ale coż poradzić... - upload plików nie odbywa się AJAX'em - oczywiście można to zrobić ładniej, ale nie teraz :P
		}
	}
}