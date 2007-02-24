<?php

class Admin_PermessiController extends Zend_Controller_Action
{
	private $acl = null;
	
	function init()
	{
		$this->acl = Zend::registry('acl_module');
		try {
			Zend::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
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
		
		$t_module = new Modules();
		$moduli = $t_module->getAttivi();
		
		$view->moduli = $moduli->toArray();

		foreach( $moduli->toArray() as $modulo ){
			if ($modulo['nome'] != 'default') var_dump( $this->acl->get($modulo['nome']) );
		}
		
		var_dump($this->acl->getRole('guest'));
		
		$view->actionTemplate = 'permessi.tpl';
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