<?php

//Zend::loadClass('Zend_Controller_Action');

//require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$view = new Sigma_View_TemplateLite();
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/default/views/scout');
		$view->title = "Campetti Specialit&agrave; Zona di Rimini v1.0";
		$view->actionTemplate = 'index.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );
		
		$acl = Zend::registry('acl_module');
		echo 'Capocampo Creare Announcement: ';
		echo $acl->isAllowed('capocampo', 'announcement', 'add') ? "allowed" : "denied"; echo '<br/>';
		echo 'Capocampo Creare Documenti: ';
		echo $acl->isAllowed('capocampo', 'documenti', 'add') ? "allowed" : "denied"; echo '<br/>';
		
		echo '<br/>';
		
		echo 'Staff Creare Announcement: ';
		echo $acl->isAllowed('staff', 'announcement', 'add') ? "allowed" : "denied"; echo '<br/>';
		echo 'Staff Creare Documenti: ';
		echo $acl->isAllowed('staff', 'documenti', 'add') ? "allowed" : "denied"; echo '<br/>';
		echo 'Staff Vedere Rubrica: ';
		echo $acl->isAllowed('staff', 'rubrica', 'index') ? "allowed" : "denied"; echo '<br/>';
		
		echo '<br/>';
		
		echo 'Member Creare Documenti: ';
		echo $acl->isAllowed('member', 'documenti', 'add') ? "allowed" : "denied"; echo '<br/>';
		echo 'Member Vedere Rubrica: ';
		echo $acl->isAllowed('member', 'rubrica', 'index') ? "allowed" : "denied"; echo '<br/>';
		echo 'Member Vedere Index: ';
		echo $acl->isAllowed('member', 'index', 'index') ? "allowed" : "denied"; echo '<br/>';
		
		echo '<br/>';
		
		echo 'Guest View Admin: ';
		echo $acl->isAllowed('guest', 'admin', 'index') ? "allowed" : "denied"; echo '<br/>';
		
		$auth_module = Zend::registry('auth_module');
		$identita = $auth_module->getIdentity();
		echo "<h5> I'm {$identita['nome']} {$identita['cognome']}, livello: {$identita['role']} </h5>";

	}
	
	public function annunciAction(){
		echo '<h2>Annunci ...</h2>';
	}

	public function noRouteAction()
	{
		$this->_redirect('/index/');
		//$this->indexAction();
	}
}

?>