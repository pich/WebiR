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
 * @package    Webir
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: ReCaptcha.php 33 2010-03-22 15:55:32Z argasek $
 */

/**
 * ReCaptcha + Polish i18n.
 *
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Service_ReCaptcha extends Zend_Service_ReCaptcha {
	/**
	 * Custom translations table
	 * 
	 * @var array
	 */
	private $_recaptchaTranslations = array(
		// Polish i18n
		'pl' => array(
			"instructions_visual" => "Przepisz słowa powyżej:",
			"instructions_audio" => "Wpisz tekst, który słyszysz:",
			"play_again" => "Odsłuchaj tekstu ponownie",
			"cant_hear_this" => "Pobierz odczytywany tekst w formacie MP3",
			"visual_challenge" => "Wersja graficzna testu",
			"audio_challenge" => "Wersja dźwiękowa testu",
			"refresh_btn" => "Inny zestaw słów",
			"help_btn" => "Pomoc",
			"incorrect_try_again" => "Słowa są niepoprawne. Spróbuj znów",
		),
	);
	
	/**
	 * Class constructor
	 *
	 * @param string $publicKey
	 * @param string $privateKey
	 * @param array $params
	 * @param array $options
	 * @param string $ip
	 * @param array|Zend_Config $params
	 */
	public function __construct($publicKey = null, $privateKey = null, $params = null, $options = null, $ip = null) {
		parent::__construct($publicKey, $privateKey, $params, $options, $ip);
		// Use the white theme
		$this->setOption('theme', 'white');
		/**
		 * Set the custom translations
		 * @todo The language should be read from application.ini!
		 */
		$this->setupCustomTranslation('pl');
		// Force ReCaptcha to output XHTML code instead of HTML
		$this->setParam('xhtml', true);
	}
	
	private function setupCustomTranslation($lang) {
		$translation = $this->getCustomTranslation($lang);
		if ($translation !== false) {
			$this->setOption('custom_translations', $translation);
		}
	}
	/**
	 * Get translation array for language $lang.
	 * Returns false, if translation for a given language doesn't exist.
	 *  
	 * @param string $lang
	 * @return array|false
	 */
	private function getCustomTranslation($lang) {
		if (isset($this->_recaptchaTranslations[$lang])) {
			return $this->_recaptchaTranslations[$lang];
		} else {
			return false;
		}
	}
}