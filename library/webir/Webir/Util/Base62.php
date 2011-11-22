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
 * @package    Webir_Util
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Base62.php 9 2010-03-12 15:24:30Z argasek $
 */

/**
 * The URL-safe BASE62 codec class.
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 *
 */
class Webir_Util_Base62 {
	const BASE = 62;
	const CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	/**
	 * Encode 32-bit integer value (2^31 - 1) using URL-safe BASE62 encoding.
	 *  
	 * @param integer $val A value to encode
	 * @param integer $base Base number, 62 by default
	 * @param string $chars Set of characters to use
	 * @return string Encoded value as string
	 */
	static function encode($val, $base = self::BASE, $chars = self::CHARS) {
		// can't handle numbers larger than  = 2147483647
		$str = '';
		do {
			$i = $val % $base;
			$str = $chars[$i] . $str;
			$val = ($val - $i) / $base;
		} while ($val > 0);
		return $str;
	}

	/**
	 * Decode BASE62 encoded integer value.
	 *  
	 * @param string $str A string to encode
	 * @param integer $base Base number, 62 by default
	 * @param string $chars Set of characters to use
	 * @return integer Original value
	 */
	static function decode($str, $base = self::BASE, $chars = self::BASE) {
		$len = strlen($str);
		$val = 0;
		$arr = array_flip(str_split($chars));
		for ($i = 0; $i < $len; ++$i) {
			$val += $arr[$str[$i]] * pow($base, $len - $i - 1);
		}
		return $val;
	}
	
}
