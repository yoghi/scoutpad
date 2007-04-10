<?php

//Zend::loadClass('Zend_Controller_Action');

//require_once 'Zend/Controller/Action.php';

class LoginController extends Zend_Controller_Action
{

	function init()
	{

		try {
			Zend_Loader::loadClass('Staff','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}
		
		$this->view = new Sigma_View_TemplateLite();
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/login.css" />';

	}

	public function indexAction()
	{
		

		$this->view->title = "Autenticati";

		$auth_module = Zend_Registry::get('auth_module');

		if ( $auth_module->hasIdentity() ){
			$this->_redirect('/');
		}

		$this->view->buttonText = 'Identifica';
		$this->view->actionTemplate = 'forms/_loginForm.tpl';

		$this->getResponse()->setBody( $this->view->render('site.tpl') );

	}

	public function outAction(){
		$auth_module = Zend_Registry::get('auth_module');
		$auth_module->clearIdentity();
		$this->_redirect('/login/');
	}

	public function inAction(){

		$this->view->title = "Login";

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$filter = Zend_Registry::get('filter');
			
			$post = $filter->filter($_POST);

			$mail = trim($post['mail']);
			$password = trim($post['password']);

			if ($mail != '' && $password != '') {

				$auth_module = Zend_Registry::get('auth_module');
				$database = Zend_Registry::get('database');
				$auth_module_adapter = new Sigma_Auth_Database_Adapter($database,array('field_password' => 'password','field_username' => 'mail','table' => 'Staff' ,'username' => $mail, 'password' => $password));

				try {

					Zend_Log::log('Provo ad autenticare : '.$mail, Zend_Log::LEVEL_DEBUG);
		
					$result = $auth_module->authenticate($auth_module_adapter);
		
					if ( $result->isValid()  ){
						$this->_redirect('/');
					}

					$this->_redirect('/errore/');
		
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}
		else {
			$this->_redirect('/');
		}

	}

	public function confirmAction(){

		$this->view->title = "Conferma";

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$filter = Zend_Registry::get('filter');
			
			$post = $filter->filter($_POST);

			$mail = trim($post['mail']);
			$cellulare = trim($post['cellulare']);
			$password = trim($post['password']);

			if ($mail != '' && $cellulare != '' && $password != '') {

				$auth_module = Zend_Registry::get('auth_module');

				try {

					$token = Zend_Registry::get('config')->auth->token;
					$password_new = sha1($token.$password);

					$s = new Staff();

					$db = $s->getAdapter();
		
					$where = $db->quoteInto('mail = ?', $mail).' and '.$db->quoteInto('cellulare = ?', $cellulare).' and password=-1 ';
		
					$user = $s->fetchRow($where);
		
					if ( $user->id === null  ){

						Zend_Log::log('Richiesta conferma di un utente non valido: '.$mail, Zend_Log::LEVEL_DEBUG);

						$this->_redirect('/errore/');
					}

					$user->password = $password_new;
		
					$user->save();
		
					Zend_Log::log('Confermato utente : '.$mail, Zend_Log::LEVEL_DEBUG);
		
					$this->_redirect('/login/');
		
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}

		$this->view->buttonText = 'Conferma';
		$this->view->actionTemplate = 'forms/_confirmForm.tpl';
		$this->getResponse()->setBody( $this->view->render('site.tpl') );

	}

	public function lostAction(){

		$this->view->title = "Lost Password";

		$auth_module = Zend_Registry::get('auth_module');

		if ( $auth_module->hasIdentity() ){
			$this->_redirect('/');
		}

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$filter = Zend_Registry::get('filter');
			
			$post = $filter->filter($_POST);

			$mail = trim($post['mail']);

			if ($mail != '') {

				try {

					$s = new Staff();

					$db = $s->getAdapter();

					$where = $db->quoteInto('mail = ?', $mail).' and password<>-1 ';

					$user = $s->fetchRow($where);

					if ( $user->id === null  ){
							
						Zend_Log::log('Richiesta re-invio password di un utente non valido: '.$mail, Zend_Log::LEVEL_DEBUG);

						$this->_redirect('/errore/');
					}
					
					$this->view->confim_text = array('Mail inviata correttamente!');
					$this->view->actionTemplate = 'ok.tpl';
			
					$this->getResponse()->setBody( $this->view->render('site.tpl') );
					
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}

		}

		$this->view->buttonText = 'Rispedisci';
		$this->view->actionTemplate = 'forms/_lostForm.tpl';

		$this->getResponse()->setBody( $view->render('site.tpl') );

	}


	public function noRouteAction()
	{
		$this->_redirect('/');
	}
}

?>