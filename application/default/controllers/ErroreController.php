<?php

class ErroreController extends Zend_Controller_Action
{
	public function init(){
		$this->view = new Sigma_View_TemplateLite();
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/index.css" />';
	}
	
	public function indexAction()
	{
		
		$this->view->title = "Errore";
		$this->view->buttonText = '';
		$this->view->actionTemplate = 'errore.tpl';
		$this->view->errore = array('Generic problem');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function privilegesAction(){
		$this->view->title = "Errore";
		$this->view->actionTemplate = 'errore.tpl';
		$this->view->errore = array('Non hai i privilegi necessari');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function fourhundredfourAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'errore.tpl';
		$this->view->errore = array('File richiesto non trovato');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function invalidAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'errore.tpl';
		$this->view->errore = array('La risorsa inserita non &egrave; valida');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function notsupportedAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'errore.tpl';
		$this->view->errore = array('La risorsa inserita non &egrave; supportata ancora...');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	

	public function noRouteAction()
	{
		$this->indexAction();
	}
}

?>