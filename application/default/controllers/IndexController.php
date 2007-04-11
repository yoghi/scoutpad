<?php

//Zend::loadClass('Zend_Controller_Action');

//require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action
{
	
	function init()
	{
		$this->view = new Sigma_View_TemplateLite();
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/double.css" />';
	}
	
	
	public function indexAction()
	{
		
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		//$this->view->actionTemplate = 'home.tpl';
		
		$auth_module = Zend_Registry::get('auth_module');
		if ( $auth_module->hasIdentity() ){  
			$identita = $auth_module->getIdentity();
			$this->view->info_user = "<h5> I'm {$identita['nome']} {$identita['cognome']}, livello: {$identita['role']} </h5>";
		}
		
		
		$this->view->actionTemplate = 'contents/index.tpl';	
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );

//		Zend_Loader::loadClass('Log','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
//		
//		$logs = new Log();
//		
//		$result = $logs->fetchAll();
		
//		foreach( $result->toArray() as $a ){
//			echo $a['message'].'<br/>';
//		}
		
		
		
//		$acl = Zend_Registry::get('acl_module');
//		echo 'Capocampo Creare Announcement: ';
//		echo $acl->isAllowed('capocampo', 'announcement', 'add') ? "allowed" : "denied"; echo '<br/>';
//		echo 'Capocampo Creare Documenti: ';
//		echo $acl->isAllowed('capocampo', 'documenti', 'add') ? "allowed" : "denied"; echo '<br/>';
//		
//		echo '<br/>';
//		
//		echo 'Staff Creare Announcement: ';
//		echo $acl->isAllowed('staff', 'announcement', 'add') ? "allowed" : "denied"; echo '<br/>';
//		echo 'Staff Creare Documenti: ';
//		echo $acl->isAllowed('staff', 'documenti', 'add') ? "allowed" : "denied"; echo '<br/>';
//		echo 'Staff Vedere Rubrica: ';
//		echo $acl->isAllowed('staff', 'rubrica', 'index') ? "allowed" : "denied"; echo '<br/>';
//		
//		echo '<br/>';
//		
//		echo 'Member Creare Documenti: ';
//		echo $acl->isAllowed('member', 'documenti', 'add') ? "allowed" : "denied"; echo '<br/>';
//		echo 'Member Vedere Rubrica: ';
//		echo $acl->isAllowed('member', 'rubrica', 'index') ? "allowed" : "denied"; echo '<br/>';
//		echo 'Member Vedere Index: ';
//		echo $acl->isAllowed('member', 'index', 'index') ? "allowed" : "denied"; echo '<br/>';
//		
//		echo '<br/>';
//		
//		echo 'Guest View Admin: ';
//		echo $acl->isAllowed('guest', 'admin', 'index') ? "allowed" : "denied"; echo '<br/>';
		
	}
	
	public function torrianaAction(){
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$this->view->actionTemplate = 'contents/torriana.tpl';
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}

	public function templateAction(){
		$this->view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$this->view->actionTemplate = 'contents/template.tpl';
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/single.css" />';
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function infoAction(){
		phpinfo();
	}
	
	public function annunciAction(){
		echo '<h2>Annunci ...</h2>';
	}
}

?>