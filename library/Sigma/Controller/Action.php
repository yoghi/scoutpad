<?php

class Sigma_Controller_Action extends Zend_Controller_Action {
	
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){
		
		parent::__construct($request,$response,$invokeArgs);
		
		/*Template Lite*/
		$this->view = new Sigma_View_TemplateLite();
		
		$auth_module = Zend_Registry::get('auth_module');
		if ( $auth_module->hasIdentity() ){  
			$identita = $auth_module->getIdentity();
			$this->view->info_user = " {$identita['nome']} {$identita['cognome']}";
			$this->view->info_level = $identita['role'];
		}
		
	}

}

?>