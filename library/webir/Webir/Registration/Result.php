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
 * @package    Webir_Registration
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id: Result.php 28 2010-03-20 19:25:16Z argasek $
 */

/**
 * 
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 *
 */
class Webir_Registration_Result {
    /**
     * General Failure
     */
    const FAILURE                          =  0;

    /**
     * Failure due to identity not being found.
     */
    const FAILURE_IDENTITY_INVALID         = -1;

    /**
     * Failure due to identity being ambiguous.
     */
    const FAILURE_IDENTITY_IN_USE          = -2;

    /**
     * Failure due to invalid credential being supplied.
     */
    const FAILURE_CREDENTIAL_INVALID       = -3;

    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_CREDENTIAL_CONFIRM_FAILS = -4;
    
    /**
     * Failur on activation (identity not found)
     */
    const FAILURE_IDENTITY_NOT_FOUND 	     = -5;
    
    /**
     * Incorrect reCAPTCHA phrase
     */
    const FAILURE_RECAPTCHA_FAILS          = -6;
    
    /**
     * Failure due to uncategorized reasons.
     */
    const FAILURE_UNCATEGORIZED            = -7;

    /**
     * Registration success.
     */
    const SUCCESS                          =  1;

    /**
     * Activation success
     */
    const ACTIVATION_SUCCESS							 =  2;
    
    /**
     * Registration success, but activation is need
     */
    const ACTIVATION_NEED                  =  3;
    
    /**
     * Registration result code
     *
     * @var int
     */
    protected $_code;

    /**
     * The identity used in the authentication attempt
     *
     * @var mixed
     */
    protected $_identity;

    /**
     * An array of string reasons why the authentication attempt was unsuccessful
     *
     * If authentication was successful, this should be an empty array.
     *
     * @var array
     */
    protected $_messages;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     * @return void
     */
    public function __construct($code, $identity, array $messages = array()) {
        $code = (int) $code;

        if ($code < self::FAILURE_UNCATEGORIZED) {
            $code = self::FAILURE;
        } elseif ($code > self::ACTIVATION_NEED ) {
            $code = 1;
        }

        $this->_code     = $code;
        $this->_identity = $identity;
        $this->_messages = $messages;
    }

    /**
     * Returns whether the result represents a successful authentication attempt
     *
     * @return boolean
     */
    public function isValid() {
        return ($this->_code > 0) ? true : false;
    }

    /**
     * getCode() - Get the result code for this authentication attempt
     *
     * @return int
     */
    public function getCode() {
        return $this->_code;
    }

    /**
     * Returns the identity used in the authentication attempt
     *
     * @return mixed
     */
    public function getIdentity() {
        return $this->_identity;
    }

    /**
     * Returns an array of string reasons why the registration attempt was unsuccessful
     *
     * If registration was successful, this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}