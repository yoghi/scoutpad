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
 * @package    Sigma_Controller
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Controller
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.1 - 2007 aprile 19 - 20:34 - Stefano Tamagnini  
 */
class Sigma_Controller_Action extends Zend_Controller_Action {
	
	/**
	 * Contiene l'identità dell'utente corrente
	 * @var array 
	 */
	protected $identita = null;
	
	/**
	 * Contiene i parametri dell'oggetto Request
	 * @var array
	 */
	protected $params = null;
	
	/**
	 * Generic Controller @see Zend_Controller_Action::__construct() for detail.
	 *
	 * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
	 */
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