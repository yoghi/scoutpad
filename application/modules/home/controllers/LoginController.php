<?php

/**
 * Scoutpad
 *
 * LICENSE
 *
 * This source file is subject to the New-BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sigma
 * @package    Sigma_Controller
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Controller
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 */
class Home_LoginController extends Sigma_Controller_Action
{

	function init()
	{

		try {
			Zend_Loader::loadClass('User','/home/workspace/Scout/ScoutPad/application/models/tables/');
			Zend_Loader::loadClass('Zend_Form');
			Zend_Loader::loadClass('Sigma_Form');
			
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}

	}
	
	public function preDispatch()
    {
    	
    	$auth_module = Zend_Registry::get('auth_module');

		if ( $auth_module->hasIdentity() ){
			// $this->_redirect('/'); equivalente a :

			if ( 'out' != $this->getRequest()->getActionName() ) {
				$this->_helper->redirector('index','index');
			}
			
		}
    	
    }

	public function indexAction()
	{

		$this->view->title = "Alt - Autenticati!";

		$this->view->HeadLink()->appendStylesheet('/styles/login.css');
		
		$form = new Sigma_Form('login');
		
        $this->view->form = $form; 

	}

	public function outAction(){
		$auth_module = Zend_Registry::get('auth_module');
		$auth_module->clearIdentity();
		$this->_helper->redirector('login','index');
	}

	public function inAction(){

		$request = $this->getRequest();

		//OLD: strtolower($_SERVER['REQUEST_METHOD']) == 'post' , meglio sfruttare il fatto di avere una richiesta HTTP
		if ($request->isPost())
		{
			
			// Get our form and validate it
			$form = new Sigma_Form( 'login' );

			if (!$form->isValid($request->getPost())) {
	            // Invalid entries
	            $this->view->form = $form;
	            $form->populate($request->getPost());
	            return $this->render('index'); //re-render the login form
	        }

			$mail = trim($_POST['username']);
			$password = trim($_POST['password']);

			if ($mail != '' && $password != '') {

				$auth_module = Zend_Registry::get('auth_module');
				$database = Zend_Registry::get('database');
				$auth_module_adapter = new Sigma_Auth_Database_Adapter($database,array('field_password' => 'password','field_username' => 'mail','table' => 'User' ,'username' => $mail, 'password' => $password));

				try {

					Zend_Registry::get('log')->log('Provo ad autenticare : '.$mail, Zend_Log::DEBUG);
		
					$result = $auth_module->authenticate($auth_module_adapter);
		
					if ( $result->isValid()  ){
						$this->_redirect('/home/');
					}

					$this->notify('/login/','errore','Autenticazione Fallita');
		
				} catch (Exception $e) {
					
					echo $e->getMessage();
					
				}
			} else $this->notify('/login/','errore','Campi vuoti','/login/');
		}
		else {
			$this->_redirect('/login/');
		}

	}

	public function confirmAction(){

		$this->view->title = "Conferma";

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{

			$mail = trim($_POST['mail']);
			$cellulare = trim($_POST['cellulare']);
			$password = trim($_POST['password']);

			if ($mail != '' && $cellulare != '' && $password != '') {

				$auth_module = Zend_Registry::get('auth_module');

				try {

					$token = Zend_Registry::get('config')->auth->token;
					$password_new = sha1($token.$password);

					$s = new User();

					$db = $s->getAdapter();
		
					$where = $db->quoteInto('mail = ?', $mail).' and '.$db->quoteInto('cellulare = ?', $cellulare).' and password=-1 ';
		
					$user = $s->fetchRow($where);
		
					if ( $user->id === null  ){

						Zend_Log::log('Richiesta conferma di un utente non valido: '.$mail, Zend_Log::LEVEL_DEBUG);

						$this->notify('/login/','errore','Spiacente conferma non valida');
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
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );

	}

	/**
	 * @todo spedire la mail...
	 */
	public function lostAction(){

		$this->view->title = "Lost Password";

		$auth_module = Zend_Registry::get('auth_module');

		if ( $auth_module->hasIdentity() ){
			$this->_redirect('/');
		}

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{

			$mail = trim($_POST['mail']);

			if ($mail != '') {

				try {

					$s = new User();

					$db = $s->getAdapter();

					$where = $db->quoteInto('mail = ?', $mail).' and password<>-1 ';

					$user = $s->fetchRow($where);

					if ( $user->id === null  ){
							
						Zend_Log::log('Richiesta re-invio password di un utente non valido: '.$mail, Zend_Log::LEVEL_DEBUG);

						$this->notify('/login/','errore','Impossibile inviare/resettare la password');
					}
					
					$this->view->confim_text = array('Mail inviata correttamente!');
					$this->view->actionTemplate = 'contents/ok.tpl';
			
					$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
					
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}

		}

		$this->view->buttonText = 'Rispedisci';
		$this->view->actionTemplate = 'forms/_lostForm.tpl';

		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );

	}


	public function noRouteAction()
	{
		$this->_redirect('/');
	}
}

?>