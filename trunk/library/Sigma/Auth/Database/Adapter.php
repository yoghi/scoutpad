<?php

/**
 * Sigma Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sigma
 * @package    Sigma_Auth_Database
 * @copyright  Copyright (c) 2006 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * Zend_Auth_Adapter
 */
require_once 'Zend/Auth/Adapter/Interface.php';



/**
 * Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';

/**
 * @category   Sigma
 * @package    Sigma_Auth_Database
 * @copyright  Copyright (c) 2006
 * @author     Stefano Tamagnini
 * @license    New BSD License
 */
class Sigma_Auth_Database_Adapter implements Zend_Auth_Adapter_Interface {
	
	/**
     * Database where take autentication info
     *
     * @var string
     */
	protected $_db;
	
	
	/**
	 * Options for manage autentication
	 * 
	 * @var array
	 */
	protected $_options;
	
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
     * @param Zend_Db_Adapter_Abstract $db database to use for autentication
     * @param  array $options
     * @throws Zend_Auth_Digest_Exception
     * @return Zend_Auth_Digest_Token
     */
	public function __construct(Zend_Db_Adapter_Abstract $database,array $options){
		
		$this->_db = $database;
		
		$optionsRequired = array('field_username','field_password','table','username', 'password');
        
        foreach ($optionsRequired as $optionRequired) {
            if (!isset($options[$optionRequired]) || !is_string($options[$optionRequired])) {
                require_once 'Zend/Auth/Database/Exception.php';
                throw new Sigma_Auth_Database_Exception("Option '$optionRequired' is required to be a string");
            }
        }
        
        $this->_options = $options;
		
	}
	
	/**
     * Performs an authentication attempt with dabase store
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
	public function authenticate(){
		
		$tokenValid    = false;
        $tokenIdentity = array();
        $tokenMessage = array();
        
        $token = Zend::registry('config')->auth->token;
        
        $pwd_r2 = sha1($token.$this->_options['password']);
        
		try {
        	$sql = 'select * from '.$this->_options['table'].' where '.$this->_options['field_username'].'=\''.$this->_options['username'].'\' and '.$this->_options['field_password'].'=\''.$pwd_r2.'\'';

        	Zend_Log::log('Autenticazione sql: '.$sql, Zend_Log::LEVEL_DEBUG);
        	
        	$data = $this->_db->fetchAll($sql);
        	
        } catch (Zend_Auth_Database_Exception $e){
        	$tokenMessage[] = 'Exception Sigma Auth: '.$e->getMessage();
        	return new Zend_Auth_Result($tokenValid, $tokenIdentity, $tokenMessage);
        }
        
        if ( !empty($data) ){
        	//ok
        	$tokenValid    = true;
        	$tokenIdentity = &$data[0];
        } else {
        	$tokenMessage[] = "Not enable for access";	
        }
        
        Zend_Log::log('Autenticazione completata con successo per '.$tokenIdentity['nome'], Zend_Log::LEVEL_INFO);

        return new Zend_Auth_Result($tokenValid, $tokenIdentity, $tokenMessage);
	}
	
}


?>