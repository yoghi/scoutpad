<?php

class PermessiController extends Zend_Controller_Action
{
	private $acl = null;
	
	function init()
	{
		$this->acl = Zend::registry('acl_module');
		
		// allowed because of inheritance from guest
		echo $this->acl->isAllowed('editor', 'announcement', 'create') ? "allowed" : "denied"; echo '<br/>';
		echo $this->acl->isAllowed('editor', 'documenti', 'create') ? "allowed" : "denied"; echo '<br/>';
		echo $this->acl->isAllowed('staff', 'announcement', 'create') ? "allowed" : "denied";

	}
	
	public function indexAction()
	{
		$view = Zend::registry('view');
		$view->title = "Permessi";
		$view->buttonText = '';
		$view->actionTemplate = 'permessi.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );
		
	}
	
	public function addAction(){}
	
	public function changeAction(){}
	
	public function deleteAction(){}

	public function noRouteAction()
	{
		$this->_redirect('/');
		//$this->indexAction();
	}
}

?>