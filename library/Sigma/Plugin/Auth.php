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
 * @version		0.0.2 - 2007 settembre 03 - 11:23 - Stefano Tamagnini  
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
								'controller' => 'notify',
								'action' => 'permission');

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
		public function preDispatch(Sigma_Controller_Request_Http $request)
		{

			$auth_session = new Zend_Session_Namespace('Zend_Auth');
			
			if ( !empty($auth_session->storage) ) {
				$token = $auth_session->storage;
				
				if ( isset($token['role']) ) {
					$rolesId = array();
					$r = explode(",",$token['role']);
					foreach($r as $role) $rolesId[] = $role;
				} else {
					$rolesId = array(1);											// (RoleId = 1) => guest
				}
						
				$id = isset($token['id']) ? intval($token['id']) : 0;				// utente inesistente o guest
			} else {
				$rolesId = array(1);
				$id = 0;
			}

			// :module/:controller/:action/*
			// :controller/:action/*
        	$module_name = $request->getModuleName();
			
        	/*
        	 * Per il Modular Directory Structure => http://framework.zend.com/manual/en/zend.controller.modular.html
			 */
			$controller = ($request->getControllerName() == '') ? 'index' : $request->getControllerName();
			$action = ($request->getActionName() == '') ? 'index' : $request->getActionName();
			
        	$log = Zend_Registry::get('log');
        	
			foreach ($rolesId as $role){
				$role_name = Sigma_Acl_Manager::getRoleName($role);
				$log->log("Il ruolo '$role_name' richiede di usare il controller '$controller' nel modulo : '$module_name' per compiere '$action'" , Zend_Log::DEBUG);
			}
			                	
        	/**
        	 * 'member' richiede di usare il controller 'torriana' nel modulo : 'default' per compiere 'index'
        	 * 'member' richiede di usare il controller 'permessi' nel modulo : 'admin' per compiere 'index' 
        	 * 
        	 * Questo significa che il sistema si accorge se il primo campo è un modulo, ma non sa se il secondo è correttamente un controller o una action di index!!
        	 * 
        	 * => se si vuole accedere a index usare index!!
        	 */

			try {
			
				// preparo il gestore ACL per un determinato utente, ruolo e mdoulo
				$acl_cache = Zend_Registry::get('config');
				$acl_manager = new Sigma_Acl_Manager($id,$rolesId,$module_name,$acl_cache->acl->cache);
				
				if ( !$acl_manager->load() ) {
					
					// non c'è in cache
					$module_name = $this->_noacl['module'];
	       			$controller = $this->_noacl['controller'];
	       			$action = $this->_noacl['action'];
	       			
	       			// forzo come pagina precedente index in quanto dovrebbe poter sempre andare!
	       			$flow_token = Sigma_Flow_Token::getInstance()->insert('/index/',array('type'=>'errore','text'=>'Problemi in cache; svuota i cookie e riprova, se il problema persiste contattaci direttamente','next'=>'/index/'));
	       			$log->log('L\'utente '.$id.' ha problemi nella propria configurazione!! (rigenerale la cache dell\'utente dopo le modifiche) ',Zend_Log::ERR);
	       			$action = 'index';
	       			$request->setParam('id',$flow_token);
	       			
				} else {
				
					$acl = $acl_manager->Acl();

					$select = false;
					
					// Ho in memoria ACL questa risorsa o non la conosco?
					if ( ! $acl->has($controller) ) {
						
						foreach ($rolesId as $role){
							if ( $acl->isAllowed($role,null,null) ) {
								$select = true;
								break;
							}
						}
						
					} else {
					
						foreach ($rolesId as $role){
						
							if ( !$acl->isAllowed($role,$controller,$action) ){
								
								// non posso accedere direttamente a quella azione ma forse posso a tutto l'oggetto...
				
								if ( !$acl->isAllowed($role,$controller,null) ) {
									
									// non posso accedere direttamente a quella risorsa ma forse posso a tutto l'ambiente ...
				
									if ( !$acl->isAllowed($role,null,null) ) {
										 
										$select = true;
										break;									
										
									}
									
								}
								
							}
						
						}
						
					}// fine else - risorsa 
					
					if ( $select ){
						
						if ( empty($auth_session->storage) ) {
							$log->log('Utente non autenticato!!', Zend_Log::DEBUG);
			       			$module_name = $this->_noauth['module'];
			       			$controller = $this->_noauth['controller'];
			       			$action = $this->_noauth['action'];
						} else {
							$log->log('Utente autenticato cmq. non gli e\' permesso l\'accesso',Zend_Log::DEBUG);
							$module_name = $this->_noacl['module'];
			       			$controller = $this->_noacl['controller'];
			       			$action = $this->_noacl['action'];
						}
					
					}
				
				}// fine else - cache
				
			
			} catch (Zend_Exception $e) {
				$module_name = $this->_noauth['module'];
       			$controller = $this->_noauth['controller'];
       			$action = $this->_noauth['action'];
       			$log->log('Eccezzione tipo Zend : '.$e->getMessage(),Zend_Log::WARN);
			} catch (Exception $e){
				$module_name = $this->_noauth['module'];
       			$controller = $this->_noauth['controller'];
       			$action = $this->_noauth['action'];
       			$log->log('Eccezzione Generica'.$e->getMessage(),Zend_Log::ERR);
			}

			/**
			 * can user see? 
			*/
			/*
			if ( ! $acl->hasPermission($controller,'R') ) {
				$module_name = $this->_noacl['module'];
       			$controller = $this->_noacl['controller'];
       			// forzo come pagina precedente index in quanto dovrebbe poter sempre andare!
       			$flow_token = Sigma_Flow_Token::getInstance()->insert($request->getFrompage(),array('type'=>'errore','text'=>'Spiacente non puoi visualizzare l\'informazione da te richiesta','next'=>'/home/'));
       			$action = 'index';
       			$request->setParam('id',$flow_token);
			}
			*/
			
			
        	$request->setModuleName($module_name);
        	$request->setControllerName($controller);
        	$request->setActionName($action);
        	
			$log->log("( Eseguo: $module_name -> $controller -> $action )", Zend_Log::NOTICE);
        	
        	foreach( $request->getParams() as $req_param_key => $req_param_value ){
        		if ( $req_param_key != 'error_handler' ) $log->log("\t".$req_param_key.' => '.$req_param_value, Zend_Log::NOTICE);
        	}
        	
	}
	                             
}

?>