<?php

//Zend::loadClass('Zend_Controller_Action');

//require_once 'Zend/Controller/Action.php';

class Admin_IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$view = new Sigma_View_TemplateLite();
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/admin/views/scout');
		$view->title = "Amministrazione";
		$view->actionTemplate = 'admin.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );
	}

	public function noRouteAction()
	{
		//$this->_redirect('/');
		$this->indexAction();
	}
}

?>