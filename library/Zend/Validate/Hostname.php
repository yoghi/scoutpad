<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Hostname.php 3278 2007-02-07 21:54:50Z darby $
 */


/**
 * @see Zend_Validate_Interface
 */
require_once 'Zend/Validate/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Hostname implements Zend_Validate_Interface
{
    /**
     * Allows Internet domain names (e.g., example.com)
     */
    const ALLOW_DNS   = 1;

    /**
     * Allows IP addresses
     */
    const ALLOW_IP    = 2;

    /**
     * Allows local network names (e.g., localhost, www.localdomain)
     */
    const ALLOW_LOCAL = 4;

    /**
     * Allows all types of hostnames
     */
    const ALLOW_ALL   = 7;

    /**
     * Default regular expression for Internet domain name validation
     */
    const REGEX_DNS_DEFAULT = '/^(?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+[a-zA-Z]{2,6}\.?$/';

    /**
     * Default regular expression for local network name validation
     */
    const REGEX_LOCAL_DEFAULT = '/^(?:[^\W_](?:[^\W_]|-){0,61}[^\W_]\.)*(?:[^\W_](?:[^\W_]|-){0,61}[^\W_])\.?$/';

    /**
     * Bit field of ALLOW constants; determines which types of hostnames are allowed
     *
     * @var integer
     */
    protected $_allow;

    /**
     * Array of regular expressions used for validation
     *
     * @var array
     */
    protected $_regex = array(
        'dns'   => self::REGEX_DNS_DEFAULT,
        'local' => self::REGEX_LOCAL_DEFAULT
        );

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Sets validator options
     *
     * @param  integer $allow
     * @return void
     */
    public function __construct($allow = self::ALLOW_ALL)
    {
        $this->setAllow($allow);
    }

    /**
     * Returns the allow option
     *
     * @return integer
     */
    public function getAllow()
    {
        return $this->_allow;
    }

    /**
     * Sets the allow option
     *
     * @param  integer $allow
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->_allow = $allow;
        return $this;
    }

    /**
     * Returns the regular expression used for validating $type hostnames
     *
     * @param  string $type
     * @return string
     */
    public function getRegex($type)
    {
        return $this->_checkRegexType($type)->_regex[$type];
    }

    /**
     * Enter description here...
     *
     * @param  string $type
     * @param  string $pattern
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    public function setRegex($type, $pattern)
    {
        $this->_checkRegexType($type)->_regex[$type] = $pattern;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the $value is a valid hostname with respect to the current allow option
     *
     * @param  mixed $value
     * @throws Zend_Validate_Exception if a fatal error occurs for validation process
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_messages = array();

        do {
            // Check input against IP address schema
            /**
             * @see Zend_Validate_Ip
             */
            require_once 'Zend/Validate/Ip.php';
            $ip = new Zend_Validate_Ip();
            if ($ip->isValid($value)) {
                if (!($this->_allow & self::ALLOW_IP)) {
                    $this->_messages[] = "'$value' appears to be an IP address but IP addresses are not allowed";
                    return false;
                } else{
                    break;
                }
            }

            // Check input against domain name schema
    		$status = @preg_match($this->_regex['dns'], $value);
            if (false === $status) {
                /**
                 * @see Zend_Validate_Exception
                 */
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Internal error: DNS validation failed');
            }

            // If the input passes as an Internet domain name, and domain names are allowed, then the hostname
            // passes validation
            if ($status && ($this->_allow & self::ALLOW_DNS)) {
                break;
            }

            // Check input against local network name schema; last chance to pass validation
            $status = @preg_match($this->_regex['local'], $value);
            if (false === $status) {
                /**
                 * @see Zend_Validate_Exception
                 */
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Internal error: local network name validation failed');
            }

            // If the input passes as a local network name, and local network names are allowed, then the
            // hostname passes validation
            $allowLocal = $this->_allow & self::ALLOW_LOCAL;
            if ($status && $allowLocal) {
                break;
            }

            // If the input does not pass as a local network name, add a message
            if (!$status) {
                $this->_messages[] = "'$value' does not appear to be a valid local network name";
            }

            // If local network names are not allowed, add a message
            if (!$allowLocal) {
                $this->_messages[] = "Local network names are not allowed";
            }

            return false;
        } while (false);
        return true;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Throws an exception if a regex for $type does not exist
     *
     * @param  string $type
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    protected function _checkRegexType($type)
    {
        if (!isset($this->_regex[$type])) {
            /**
             * @see Zend_Validate_Exception
             */
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("'$type' must be one of ('" . implode(', ', array_keys($this->_regex))
                                            . "')");
        }
        return $this;
    }
}
