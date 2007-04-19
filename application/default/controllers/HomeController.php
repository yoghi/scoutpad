<?php

class HomeController extends Sigma_Controller_Action
{	
	
	public function indexAction(){
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$this->view->actionTemplate = 'contents/home.tpl';	
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}
	
	public function torrianaAction(){
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$this->view->actionTemplate = 'contents/torriana.tpl';
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}
	
	public function faqAction(){
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$this->view->actionTemplate = 'contents/faq.tpl';
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}

	public function templateAction(){
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$this->view->actionTemplate = 'contents/template.tpl';
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/single.css" />';
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}

}

?>