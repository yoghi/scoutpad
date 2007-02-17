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
 * @package    Zend_Auth
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Adapter.php 2794 2007-01-16 01:29:51Z bkarwin $
 */


/**
 * Zend_Auth_Adapter
 */
require_once 'Zend/Auth/Adapter.php';


/**
 * Zend_Auth_Database_Token
 */
require_once 'Sigma/Auth/Database/Token.php';


/**
 * @category   Zend
 * @package    Zend_Auth
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sigma_Auth_Database_Adapter extends Zend_Auth_Adapter
{
    /**
     * Database where take autentication info
     *
     * @var string
     */
    protected $_db;

    /**
     * Creates a new digest authentication object against the Abrastract Database provided
     *
     * @param  string $filename
     * @throws Zend_Auth_Digest_Exception
     * @return void
     */
    public function __construct(Zend_Db_Adapter_Abstract $database)
    {
        $this->_db = $database;
    }

    /**
     * Authenticates against the given parameters
     *
     * $options requires the following key-value pairs:
     *
     * 	    'field_username' => field where is username
     * 		'field_password' => field where control password
     * 		'database' => Zend_Db object
     * 		'table'    => table name where check autentication
     *      'username' => digest authentication user
     *      'password' => user password 
     *
     * @param  array $options
     * @throws Zend_Auth_Digest_Exception
     * @return Zend_Auth_Digest_Token
     */
    public static function staticAuthenticate(array $options)
    {
        $optionsRequired = array('field_username','field_password','table','username', 'password');
        
        foreach ($optionsRequired as $optionRequired) {
            if (!isset($options[$optionRequired]) || !is_string($options[$optionRequired])) {
                require_once 'Zend/Auth/Database/Exception.php';
                throw new Sigma_Auth_Database_Exception("Option '$optionRequired' is required to be a string");
            }
        }
        
        $tokenValid    = false;
        $tokenIdentity = array();
        
        $token = Zend::registry('config')->auth->token;
        
        $pwd_r2 = sha1($token.$options['password']);
        
        try {
        	$sql = 'select * from '.$options['table'].' where '.$options['field_username'].'=\''.$options['username'].'\' and '.$options['field_password'].'=\''.$pwd_r2.'\'';

        	Zend_Log::log('Autenticazione sql: '.$sql, Zend_Log::LEVEL_DEBUG);
        	
        	$data = $options['database']->fetchAll($sql);
        	
        } catch (Zend_Auth_Database_Exception $e){
        	$tokenMessage = 'Exception: '.$e->getMessage();
        	return new Sigma_Auth_Database_Token($tokenValid, $tokenIdentity, $tokenMessage);
        }
        
        $tokenMessage = "Not enable for access";
        
        if ( !empty($data) ){
        	//ok
        	$tokenValid    = true;
        	$tokenIdentity = &$data[0];
        }
        
        Zend_Log::log('Autenticazione completata con successo per '.$tokenIdentity['nome'], Zend_Log::LEVEL_INFO);

        return new Sigma_Auth_Database_Token($tokenValid, $tokenIdentity, $tokenMessage);
    }

    /**
     * Authenticates the realm, username and password given
     *
     * $options requires the following key-value pairs:
     *
     * 		'table'    => table where check autentication
     *      'username' => digest authentication user
     *      'password' => password for the user
     *
     * @param  array $options
     * @uses   Zend_Auth_Digest_Adapter::staticAuthenticate()
     * @return Zend_Auth_Digest_Token
     */
    public function authenticate(array $options)
    {
    	$options['database'] = $this->_db;
        return self::staticAuthenticate($options);
    }

}
