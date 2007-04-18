<?php

class Sigma_Controller_Action extends Zend_Controller_Action {
	
	protected $identita = null;
	protected $params = null;
	
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){
		
		$this->params = $request->getParams();
		
		// NB: se chiamo il costruttore del padre, egli chiamera la procedura di INIT sua e di tutti i figli (nostri compresi)
		parent::__construct($request,$response,$invokeArgs);
		
		/*Template Lite*/
		$this->view = new Sigma_View_TemplateLite();
		
		/*
		$auth_module = Zend_Registry::get('auth_module');
		if ( $auth_module->hasIdentity() ){  
			$this->identita = $auth_module->getIdentity();
			$this->view->info_user = " {$this->identita['nome']} {$this->identita['cognome']}";
			$this->view->info_level = $this->identita['role'];
		}
		*/

		$auth_session = new Zend_Session_Namespace('Zend_Auth');
		if ( !empty($auth_session->storage) ) {
			$this->identita = $auth_session->storage;
			$this->view->info_user = " {$this->identita['nome']} {$this->identita['cognome']}";
			$this->view->info_level = $this->identita['role'];
		}
		
		$this->view->base_url = '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName();
		
		//$this->view->before_page = $sigma_flow->before_page;
		$this->view->before_page = '';
		
	}

}

?>