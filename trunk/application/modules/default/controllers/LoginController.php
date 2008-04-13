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
class LoginController extends Sigma_Controller_Action
{

	function init()
	{

		try {
			Zend_Loader::loadClass('User','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}

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

		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );

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

			$mail = trim($_POST['mail']);
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