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
        	$module = $request->getModuleName();
			
        	/*
        	 * Per il Modular Directory Structure => http://framework.zend.com/manual/en/zend.controller.modular.html
			 */
			$controller = ($request->getControllerName() == '') ? 'index' : $request->getControllerName();
			$action = ($request->getActionName() == '') ? 'index' : $request->getActionName();
			
			
        	$log = Zend_Registry::get('log');
        	
        	$log->log("'$role' richiede di usare il controller '$controller' per compiere '$action' nel modulo : '$module'" , Zend_Log::DEBUG);

			if ( $module != 'default' ) {
				if ( !$this->_auth->hasIdentity() ) { 
					$log->log('Utente non autenticato!!', Zend_Log::DEBUG);
	       			$module = $this->_noauth['module'];
	       			$controller = $this->_noauth['controller'];
	       			$action = $this->_noauth['action'];
	        	} 
			}
			
        	$log->log("Eseguo: $module _ $controller -> $action", Zend_Log::DEBUG);
        	
        	$request->setModuleName($module);
        	$request->setControllerName($controller);
        	$request->setActionName($action);
	}
	                             
}

?>