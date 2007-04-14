<?php

class Admin_PermessiController extends Sigma_Controller_Action 
{
	private $acl = null;
	
	function init()
	{
		//$this->acl = Zend_Registry::get('acl_module');
		try {
			Zend_Loader::loadClass('Acl','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}
	}
	
	public function indexAction()
	{
		$view = new Sigma_View_TemplateLite();
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/admin/views/scout');
		$view->title = "Permessi";
		$view->actionTemplate = 'permessi.tpl';
		
		
		/*
		 * TABELLA ACL (Esempio)
		   id 	Modulo 	Controller 	Action 	Role
			1 	default 	index 	NULL 	NULL
			2 	admin 	permessi 	NULL 	NULL
		 */
		
		
		$acl = new Acl();
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		$this->getResponse()->setBody( $view->render('site.tpl') );
		
	}
	
	public function addAction(){}
	
	public function changeAction(){}
	
	public function deleteAction(){}

	public function noRouteAction()
	{
		//$this->_redirect('/');
		$this->indexAction();
	}
}

?>