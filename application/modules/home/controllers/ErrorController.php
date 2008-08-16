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
//class IndexController extends Sigma_Controller_Action
class Home_ErrorController extends Zend_Controller_Action
{

	public function errorAction()
	{
		$this->_helper->layout->disableLayout();
		
		$errors = $this->_getParam('error_handler');

		// Clear previous content
		$this->getResponse()->clearBody();
		
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
				
				// 404 error -- controller or action not found
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
				$this->view->content = '404 - Page Not Found';
				
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				
				$this->view->content = '501 - Not Implemented';
				
				break;
			default:
				// application error
				$this->view->content = 'Siamo spiacenti ma un errore interno ha portato all\'impossibilit&agrave; di completare la richiesta';
				break;
		}

	}


}

?>