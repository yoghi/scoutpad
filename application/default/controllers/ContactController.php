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
class ContactController extends Sigma_Controller_Action
{

	public function indexAction()
	{
		$this->view->title = "Contattaci";
		$this->view->actionTemplate = 'contents/contact.tpl';
		$this->view->buttonText = 'Invia';
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}

	/**
	 * Invio della richiesta via mail
	 */
	public function inviaAction(){

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{

			Zend_Loader::loadClass('Zend_Validate_Digits');
			Zend_Loader::loadClass('Zend_Filter_HtmlEntities');
			Zend_Loader::loadClass('Zend_Validate_EmailAddress');
			
			
			$filtro = new Zend_Filter_HtmlEntities(ENT_COMPAT,'UTF-8');
			
			$z = new Zend_Validate_Digits();
			$m = new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS,false); //non controllo gli MX (questa mail non deve per forza essere spedita
			
			if ( !$m->isValid($_POST['mail']) ) { //controllo mail
				$this->_redirect('/errore/');
			}
			 
			if ( !$z->isValid($_POST['telefono']) ) { //controllo telefono
				$this->_redirect('/errore/');
			}
			
			$mail = trim($_POST['mail']);
			$telefono = trim($_POST['telefono']);
			$nomecognome = $filtro->filter(trim($_POST['nomecognome']));
			$richiesta = $filtro->filter(trim($_POST['richiesta']));

			if ($nomecognome != '' && $richiesta != '' ){
				
				$this->view->chiamante = $_SERVER['REMOTE_ADDR']; //chi ha richiesto la pagina
				$this->view->nomecognome = $nomecognome;
				$this->view->richiesta = $richiesta;
				$this->view->telefono = $telefono;
				$this->view->mail = $mail;

				try {
					
					//send a mail ...

					Zend_Loader::loadClass('Zend_Mail');
					Zend_Loader::loadClass('Zend_Mail_Transport_Smtp');
					
					$config = new Zend_Config_Ini(CONFIG_FILE, 'mail');
					$transport = new Zend_Mail_Transport_Smtp($config->server, $config->toArray());
					$mail_server = new Zend_Mail();
			
					$testo = $this->view->render('modelli/aiuto.tpl');

					$mail_server->setBodyText( "Sommario : \n $testo\n" );

					$mail_server->setFrom($mail, "$nomecognome");
					$mail_server->addTo('root@lanzanoven.net', 'Test');
					$mail_server->setSubject('Richiesta di aiuto');
					//$mail_server->send($transport);
					
					//$this->getRequest()->getRequestUri()
					echo $this->notify()...
					//$this->_redirect('/errore/complete/');
					
				} catch (Exception $e) {
					
					echo $e->getMessage();
					//$this->_redirect('/errore/');

				}
				
			} else {
				$this->_redirect('/errore/missing/');
			}
			
		}
		else {
			$this->_redirect('/');
		}

	}

	public function noRouteAction()
	{
		$this->indexAction();
	}
}

?>