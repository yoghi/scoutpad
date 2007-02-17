<?php

class Sigma_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

		private $_noauth = array('module' => null,
								'controller' => 'login',
								'action' => 'index');

		private $_noacl = array('module' => null,
								'controller' => 'errore',
								'action' => 'privileges');

		private $_auth = null;
		private $_acl = null;
				
		public function __construct(){
			$this->_auth = Zend::registry('auth_module');
			//$this->_acl = Zend::registry('acl_module');
		}
	       
		public function preDispatch($request)
		{
			if ($this->_auth->isLoggedIn() ) {
				$token = $this->_auth->getToken()->getIdentity();
				$role = isset($token['role']) ? $token['role'] : 'guest';
				//->getUser()->role;
			} else {
				$role = 'guest';
			}

			$controller = ($request->getControllerName() == '') ? 'index' : $request->getControllerName();

			$action = ($request->getActionName() == '') ? 'index' : $request->getActionName();
     
			/*
        	 * Nota bene che se si setta un 'module' si ottiene che la classe AdminController diventi Default_AdminController
        	 * bisogna stare attenti!!!
        	 */
        	$module = $request->getModuleName();
        	
        	Zend::loadClass('Zend_Acl');
			Zend::loadClass('Zend_Acl_Role');
			Zend::loadClass('Zend_Acl_Resource');

			try {
				Zend::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			}
			catch (Zend_Exception $e) {
				var_dump($e);
			}
			
			if ( $module === null ){
				
				$t_module = new Modules();
				
				$default_module = $t_module->getModuleByName('default')->toArray();

				if ( empty($default_module[0]['acl']) ) {
					
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
					
					// Editor inherits from staff
					$acl->addRole(new Zend_Acl_Role('capocampo'), 'responsabile');
					
					// Administrator does not inherit access controls
					$acl->addRole(new Zend_Acl_Role('administrator'));
					
					// TUTTI
					$acl->add(new Zend_Acl_Resource('login'));		// public login
					$acl->add(new Zend_Acl_Resource('index'));		// public home
					
					// MEMBRI DELLA STAFF
					$acl->add(new Zend_Acl_Resource('announcement'));		// announcement
					$acl->add(new Zend_Acl_Resource('documenti'));			// documenti
					$acl->add(new Zend_Acl_Resource('rubrica'));			// rubrica
					
					$acl->add(new Zend_Acl_Resource('campetti'));			// campetti
					
					$acl->add(new Zend_Acl_Resource('admin'));				// admin
					$acl->add(new Zend_Acl_Resource('permessi'));			// permessi
					
					//NB: se non settato è denied di default.
					
					// Questa viene sempre eseguita per qualunque Role/Utente
					//$acl->allow(null, null, null, new CleanIPAssertion());
					
					// Administrator inherits nothing, but is allowed all privileges
					$acl->allow('administrator');
					
					if ( $module === null ) {
						
						// Guest may only view content in generale
						$acl->allow($roleGuest, 'index', 'index');
						$acl->allow($roleGuest, 'login', null); //array('in','out','confirm',...)
						
						$acl->allow($roleMember,'index','index');
						
						// Staff inherits view privilege from guest, but also needs additional privileges
						$acl->allow('staff', array('announcement','documenti'), array('add','edit','submit','index'));
						
		//				// Responsabile 
		//				$acl->allow('responsabile', 'campetti', array('iscrivi','deiscrivi'));
		//				
		//				// CapoCampo inherits view, edit, submit, and revise privileges from staff, but also needs additional privileges
		//				$acl->allow('capocampo',array( 'campetti'), array('add','edit','submit'));	
		//				$acl->allow('capocampo', array('announcement','rubrica','documenti'), array('publish','delete'));
						
					}
					
					if ( $module == 'rubrica' ) {
						$acl->allow($roleStaff,'index',array('index','edit','submit','index'));
					}
					
					$data = array(
							'acl' => base64_encode(serialize($acl))
						);
					$where = 'id = ' . $default_module[0]['id'];
					
					$t_module->update($data,$where);
					
				}
				
				$acl = $default_module[0]['acl'];
				
			}
			
			Zend::register('acl_module', $acl );
       		
       		$this->_acl = $acl;
        	
        	
        	$resource = $controller;

        	if (!$this->_acl->has($resource)) {
        		$resource = null;
        	}
        	
        	Zend_Log::log("'$role' richiede la risorsa '$resource' per compiere '$action' nel modulo : '$module'" , Zend_Log::LEVEL_DEBUG);

			if ( !$this->_auth->isLoggedIn() && $resource != 'login' && ( $module != null || $resource != 'index' ) ) {
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
			
        	Zend_Log::log("Eseguo: $module _ $controller -> $action", Zend_Log::LEVEL_DEBUG);
        	
        	$request->setModuleName($module);
        	$request->setControllerName($controller);
        	$request->setActionName($action);

	}
	                             
}

?>