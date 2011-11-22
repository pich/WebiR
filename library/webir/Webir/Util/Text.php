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
 * @version    $Id: Text.php 66 2010-03-29 09:53:14Z argasek $
 */

class Webir_Util_Text {
	
	/**
	 * Returns var_dump($variable) output as string.
	 * 
	 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
	 * @return string
	 */
	static public function varDump($variable) {
		ob_start();
		var_dump($variable);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	/**
	 * Returns beautified JSON output.
	 * 
	 * @author Umbrae <umbrae@gmail.com>
	 * @return string 
	 */
	static public function jsonPrettyPrint($json) {
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;

		$json_obj = json_decode($json);

		if($json_obj === false)
		return false;

		$json = json_encode($json_obj);
		$len = strlen($json);

		for($c = 0; $c < $len; $c++) {
			$char = $json[$c];
			switch($char)			{
				case '{':
				case '[':
					if(!$in_string)					{
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					}	else {
						$new_json .= $char;
					}
					break;
				case '}':
				case ']':
					if(!$in_string) {
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					}	else {
						$new_json .= $char;
					}
					break;
				case ',':
					if(!$in_string)	{
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					} else {
						$new_json .= $char;
					}
					break;
				case ':':
					if(!$in_string)	{
						$new_json .= ": ";
					}	else {
						$new_json .= $char;
					}
					break;
				case '"':
					if($c > 0 && $json[$c-1] != '\\')	{
						$in_string = !$in_string;
					}
				default:
					$new_json .= $char;
					break;
			}
		}

		return $new_json;
	}


	/**
	 * Takes xml as a string and returns it nicely indented
	 *
	 * @author Will Bond
	 * @param string $xml The xml to beautify
	 * @param boolean $html_output If the xml should be formatted for display on an html page
	 * @return string The beautified xml
	 */
	static public function xmlPrettyPrint($xml, $html_output=FALSE) {
		$xml_obj = new SimpleXMLElement($xml);
		$xml_lines = explode("\n", $xml_obj->asXML());
		$indent_level = 0;

		$new_xml_lines = array();
		foreach ($xml_lines as $xml_line) {
			if (preg_match('#^(<[a-z0-9_:-]+((\s+[a-z0-9_:-]+="[^"]+")*)?>.*<\s*/\s*[^>]+>)|(<[a-z0-9_:-]+((\s+[a-z0-9_:-]+="[^"]+")*)?\s*/\s*>)#i', ltrim($xml_line))) {
				$new_line = str_pad('', $indent_level*4) . ltrim($xml_line);
				$new_xml_lines[] = $new_line;
			} elseif (preg_match('#^<[a-z0-9_:-]+((\s+[a-z0-9_:-]+="[^"]+")*)?>#i', ltrim($xml_line))) {
				$new_line = str_pad('', $indent_level*4) . ltrim($xml_line);
				$indent_level++;
				$new_xml_lines[] = $new_line;
			} elseif (preg_match('#<\s*/\s*[^>/]+>#i', $xml_line)) {
				$indent_level--;
				if (trim($new_xml_lines[sizeof($new_xml_lines)-1]) == trim(str_replace("/", "", $xml_line))) {
					$new_xml_lines[sizeof($new_xml_lines)-1] .= $xml_line;
				} else {
					$new_line = str_pad('', $indent_level*4) . $xml_line;
					$new_xml_lines[] = $new_line;
				}
			} else {
				$new_line = str_pad('', $indent_level*4) . $xml_line;
				$new_xml_lines[] = $new_line;
			}
		}

		$xml = join("\n", $new_xml_lines);
		return ($html_output) ? '<pre>' . htmlentities($xml) . '</pre>' : $xml;
	}

}