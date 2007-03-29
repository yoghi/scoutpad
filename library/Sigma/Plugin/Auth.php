<?php

class Sigma_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

		private $_noauth = array('module' => 'default',
								'controller' => 'login',
								'action' => 'index');

		private $_noacl = array('module' => 'default',
								'controller' => 'errore',
								'action' => 'privileges');

		private $_auth = null;
		private $_acl = null;
				
		public function __construct(){
			
			$this->_auth = Zend_Registry::get('auth_module');
			
			Zend_Loader::loadClass('Zend_Acl');
			Zend_Loader::loadClass('Zend_Acl_Role');
			Zend_Loader::loadClass('Zend_Acl_Resource');
			
		}
	       
		public function preDispatch($request)
		{
			if ( $this->_auth->hasIdentity() ) {
				$token = $this->_auth->getIdentity();
				$role = isset($token['role']) ? $token['role'] : 'guest';
			} else {
				$role = 'guest';
			}

			// :module/:controller/:action/*
			// :controller/:action/*
			/*
        	 * Nota bene che se si setta un 'module' si ottiene che la classe AdminController diventi Default_AdminController
        	 * bisogna stare attenti!!!
        	 */
        	$module = $request->getModuleName();
			
			$controller = ($request->getControllerName() == '') ? 'index' : $request->getControllerName();
			$action = ($request->getActionName() == '') ? 'index' : $request->getActionName();
			
			/*
			 * Devo caricare i permessi in modo da poter eseguire il controllo sulla richiesta prima ancora di passare il controllo a chi di dovere 
			 */
			
			//Attori (+ struttura)
			$acl = new Zend_Acl();
			$roleGuest = new Zend_Acl_Role('guest');
			$acl->addRole($roleGuest);
			
			//member
			$roleMember = new Zend_Acl_Role('member'); 
			$acl->addRole($roleMember, $roleGuest);
			
			// la staff eredita le proprietà del guest
			$roleStaff = new Zend_Acl_Role('staff'); 
			$acl->addRole($roleStaff, $roleGuest);
			
			// il responsabile
			$roleResponsabile = new Zend_Acl_Role('responsabile');
			$acl->addRole($roleResponsabile,$roleStaff);
			
			// CapoCampo inherits from staff
			$acl->addRole(new Zend_Acl_Role('capocampo'), 'responsabile');
			
			// Administrator does not inherit access controls
			$acl->addRole(new Zend_Acl_Role('administrator'));
			
			//CASO A => default [letti da tutti, sono quelli comuni ??]
			
			$acl->add(new Zend_Acl_Resource('login'));		// public login
			$acl->add(new Zend_Acl_Resource('errore'));		// public login
			$acl->add(new Zend_Acl_Resource('index'));		// public home
			
			$acl->allow($roleGuest, 'index', 'index'); //posso eseguire ciò che voglio sulla index
			$acl->allow($roleGuest, 'login', null); //posso eseguire ciò che voglio sul login
			$acl->allow($roleGuest, 'errore', null); //posso eseguire ciò che voglio sull'errore
			
			//CASO B => modulo installato 
			
			//CASO C => modulo inesistente
			
			$this->_acl = $acl; //new Zend_Acl();
			Zend_Registry::set('acl_module', $acl );
       		
        	$resource = $controller;

        	if (!$this->_acl->has($resource)) {
        		throw new Zend_Controller_Exception('[ACL] Resource: '.$resource.' not present');
        	}
        	
        	Zend_Log::log("'$role' richiede la risorsa '$resource' per compiere '$action' nel modulo : '$module'" , Zend_Log::LEVEL_DEBUG);

			//if ( !$this->_auth->hasIdentity() && $resource != 'login' && ( $module != null || $resource != 'index' ) ) {
			if ( $module != 'default' ) {
				if ( !$this->_auth->hasIdentity() ) { 
					Zend_Log::log('Utente non autenticato!!', Zend_Log::LEVEL_DEBUG);
	       			$module = $this->_noauth['module'];
	       			$controller = $this->_noauth['controller'];
	       			$action = $this->_noauth['action'];
	        	} else {
		        	if (!$this->_acl->isAllowed($role, $resource, $action)) {
		        		Zend_Log::log('Permesso alla risorsa negato!!', Zend_Log::LEVEL_DEBUG);
		       			$module = $this->_noacl['module'];
		       			$controller = $this->_noacl['controller'];
		       			$action = $this->_noacl['action'];
		        	} 
	        	}
			}
			
        	Zend_Log::log("Eseguo: $module _ $controller -> $action", Zend_Log::LEVEL_DEBUG);
        	
        	$request->setModuleName($module);
        	$request->setControllerName($controller);
        	$request->setActionName($action);

	}
	                             
}

?>