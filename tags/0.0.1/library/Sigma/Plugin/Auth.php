<?php

/**
 * Scoutpad
 *
 * LICENSE
 *
 * This source file is subject to the New-BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sigma
 * @package    Sigma_Plugin
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Plugin
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.1 - 2007 aprile 19 - 20:34 - Stefano Tamagnini  
 */
class Sigma_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

		/**
		 * Valori da usare nel caso l'utente non sia autorizzato (guest) e non sia autenticato
		 * @var array
		 */
		private $_noauth = array('module' => 'default',
								'controller' => 'login',
								'action' => 'index');

		/**
		 * Valori da usare nel caso l'utente autenticato non sia autorizzato
		 * @var array
		 */
		private $_noacl = array('module' => 'default',
								'controller' => 'errore',
								'action' => 'privileges');

		/**
		 * Questa classe è un Plugin da eseguire prima di passare il controllo ad un Controller specifico in modo da poter controllare con le ACL l'accesso alle risorse.
		 * @throws Zend_Exception
		 */
		public function __construct(){
			
			try {
				Zend_Loader::loadClass('Zend_Acl');
				Zend_Loader::loadClass('Zend_Acl_Role');
				Zend_Loader::loadClass('Zend_Acl_Resource');
				Zend_Loader::loadClass('Sigma_Acl_Manager');
			} catch( Zend_Exception $e ){
				throw $e;
			}
			
		}

		/**
		 * @see 	Zend_Controller_Plugin_Abstract::preDispatch()
		 * @param	Zend_Controller_Request_Abstract $request
		 * @return	void
		 */
		public function preDispatch(Zend_Controller_Request_Abstract $request)
		{

			$auth_session = new Zend_Session_Namespace('Zend_Auth');
			
			if ( !empty($auth_session->storage) ) {
				$token = $auth_session->storage;
				$role = isset($token['role']) ? $token['role'] : 'guest';
			} else {
				$role = 'guest';
			}

			// :module/:controller/:action/*
			// :controller/:action/*
        	$module = $request->getModuleName();
			
        	/*
        	 * Per il Modular Directory Structure => http://framework.zend.com/manual/en/zend.controller.modular.html
			 */
			$controller = ($request->getControllerName() == '') ? 'index' : $request->getControllerName();
			$action = ($request->getActionName() == '') ? 'index' : $request->getActionName();
			
			
        	$log = Zend_Registry::get('log');
        	
        	$log->log("'$role' richiede di usare il controller '$controller' nel modulo : '$module' per compiere '$action'" , Zend_Log::DEBUG);
        	
        	/**
        	 * 'member' richiede di usare il controller 'torriana' nel modulo : 'default' per compiere 'index'
        	 * 'member' richiede di usare il controller 'permessi' nel modulo : 'admin' per compiere 'index' 
        	 * 
        	 * Questo significa che il sistema si accorge se il primo campo è un modulo, ma non sa se il secondo è correttamente un controller o una action di index!!
        	 * 
        	 * => se si vuole accedere a index usare index!!
        	 */

        	//$acl_cache = new AclCache();        	
			//$r = $acl_cache->fetchAll($where)->toArray();

			$acl_manager = new Sigma_Acl_Manager($role,$module);
			
			if ( !$acl_manager->loadFromCache() ) {
				// non c'è in cache
				$module = $this->_noacl['module'];
       			$controller = $this->_noacl['controller'];
       			$action = $this->_noacl['action'];
			} else {
			
				$acl = $acl_manager->getAcl();
				
				if ( ! $acl->has($controller) ) {
					
					if ( !$acl->isAllowed($role,null,null) ) {
								 
						if ( empty($auth_session->storage) ) {
							$log->log('Utente non autenticato!!', Zend_Log::DEBUG);
			       			$module = $this->_noauth['module'];
			       			$controller = $this->_noauth['controller'];
			       			$action = $this->_noauth['action'];
						} else {
							$module = $this->_noacl['module'];
			       			$controller = $this->_noacl['controller'];
			       			$action = $this->_noacl['action'];
						}
						
					}
					
				} else {
				
					if ( !$acl->isAllowed($role,$controller,$action) ){
						
						// non posso accedere direttamente a quella azione ma forse posso a tutto l'oggetto...
		
						if ( !$acl->isAllowed($role,$controller,null) ) {
							
							// non posso accedere direttamente a quella risorsa ma forse posso a tutto l'ambiente ...
		
							if ( !$acl->isAllowed($role,null,null) ) {
								 
								if ( empty($auth_session->storage) ) {
									$log->log('Utente non autenticato!!', Zend_Log::DEBUG);
					       			$module = $this->_noauth['module'];
					       			$controller = $this->_noauth['controller'];
					       			$action = $this->_noauth['action'];
								} else {
									$module = $this->_noacl['module'];
					       			$controller = $this->_noacl['controller'];
					       			$action = $this->_noacl['action'];
								}
								
							}
							
						}
						
					}
					
				}
			
			}

        	$log->log("Eseguo: $module _ $controller -> $action", Zend_Log::DEBUG);
        	
        	$request->setModuleName($module);
        	$request->setControllerName($controller);
        	$request->setActionName($action);
	}
	                             
}

?>