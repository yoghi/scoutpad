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
class Admin_IndexController extends Sigma_Controller_Action
{
	public function indexAction()
	{
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/admin/views/scout');
		$view->title = "Amministrazione";
		$view->actionTemplate = 'admin.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );
	}
	
	public function infoAction(){
		phpinfo();
	}

	public function noRouteAction()
	{
		//$this->_redirect('/');
		$this->indexAction();
	}
}

?>