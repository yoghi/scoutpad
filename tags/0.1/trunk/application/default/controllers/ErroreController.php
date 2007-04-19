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
class ErroreController extends Sigma_Controller_Action
{
	
	public function indexAction()
	{
		
		$this->view->title = "Errore";
		$this->view->buttonText = '';
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('Generic problem');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function privilegesAction(){
		$this->view->title = "Errore";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('Non hai i privilegi necessari');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function fourhundredfourAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('File richiesto non trovato');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function invalidAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('La risorsa inserita non &egrave; valida');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function notsupportedAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('La risorsa inserita non &egrave; supportata ancora...');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function missingAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('Campo obbligatorio mancante! Riprovare inserendo tutti i campi necessari');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function toobigAction(){
		$params = $this->_getAllParams();
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('La risorsa inserita &egrave; troppo grande. Dimensione massima '.$params['maxsize'].' bytes');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function existAction(){
		$params = $this->_getAllParams();
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('La risorsa inserita esiste gi&agrave;');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	
	public function notavaibleAction(){
		$this->view->title = "Errore 404";
		$this->view->actionTemplate = 'contents/errore.tpl';
		$this->view->errore = array('Il sistema non &egrave; disponibile in questo momento. riprovare pi&ugrave; tardi');
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}

	public function noRouteAction()
	{
		$this->indexAction();
	}
}

?>