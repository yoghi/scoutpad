<?php

class ErroreController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$view = new Sigma_View_TemplateLite();
		$view->title = "Errore";
		$view->buttonText = '';
		$view->actionTemplate = 'errore.tpl';
		$view->errore = array('errore 404');
		$this->getResponse()->setBody( $view->render('site.tpl') );
	}
	
	public function privilegesAction(){
		$view = new Sigma_View_TemplateLite();
		$view->title = "Errore";
		$view->actionTemplate = 'errore.tpl';
		$view->errore = array('errore non hai i privilegi necessari');
		$this->getResponse()->setBody( $view->render('site.tpl') );
	}
	
	public function fourhundredfourAction(){
		$view = new Sigma_View_TemplateLite();		
		$view->title = "Errore 404";
		$view->actionTemplate = 'errore.tpl';
		$view->errore = array('errore file richiesto non trovato');
		$this->getResponse()->setBody( $view->render('site.tpl') );
	}
	

	public function noRouteAction()
	{
		$this->indexAction();
	}
}

?>