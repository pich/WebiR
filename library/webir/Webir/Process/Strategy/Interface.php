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
 * @package    Webir_Process
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Interface.php 87 2010-04-01 10:54:12Z argasek $
 */

/**
 * An interface for process execution, termination and is-alive checking.  
 *   
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * 
 */
interface Webir_Process_Strategy_Interface {	
	/**
	 * Execute the process
	 * 
	 * @param boolean $background Set whether run process in background or not. Default: run in background. 
	 * @return integer|false PID of process or false, if running the process failed
	 */
	public function execute($background = true);
	
	/**
	 * Checks if the process of given PID is running.  
	 * 
	 * @param integer $pid PID of process
	 * @return boolean True, if the process is running 
	 */
	public function isAlive($pid);
	
	/**
	 * Kills the process of given PID.
	 * 
	 * @param integer $pid PID of process.
	 * @return boolean True, if killing the process succeeded.
	 */
	public function kill($pid);

}
