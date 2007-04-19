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
class Rubrica_IndexController extends Sigma_Controller_Action
{

	function init()
	{
		try {
			Zend_Loader::loadClass('Staff','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}
	}

	function indexAction()
	{
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/rubrica/views/scout');
		$view->title = "Elenco membri";
		$staff = new Staff();
		
		//$view->membri = $staff->fetchAll();
		
		$view->membri = $staff->getAttivi()->toArray();
		$view->membri_ombra = $staff->getCollaboratori()->toArray();
		
		$view->actionTemplate = 'rubrica.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );
		
	}
	
	function addAction()
	{
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/rubrica/views/scout');
		$view->title = "Aggiungi un nuovo membro";

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$filter = Zend_Registry::get('filter');
			
			$post = $filter->filter($_POST);
			
			$nome = trim($post['nome']);
			$cognome = trim($post['cognome']);
			$mail = trim($post['mail']);
			$cellulare = trim($post['cellulare']);
			$fisso = trim($post['fisso']);
			$gruppo = trim($post['gruppo']);
			$status = trim($post['status']);
			
			if ($cognome != '' && $nome != '') {
					$data = array(
						'nome' => $nome,
						'cognome' => $cognome,
						'fisso' => $fisso,
						'gruppo' => $gruppo,
						'cellulare' => $cellulare,
						'mail' => $mail,
						'status' => $status
					);
					try {
						$staff = new Staff();
						$staff->insert($data);
					}
					catch( Zend_Exception $e){
						Zend_Log::log($e->getMessage(),Zend_Log::LEVEL_WARNING);
					}
					$url = '/rubrica/';
					$this->_redirect($url);
					return;
			}
		}

		// set up an "empty" album
		$staff = new Staff();
		$staff->id = '';
		$staff->nome = '';
		$staff->cognome = '';
		
		$view->staff = $staff->find(-1)->toArray();

		// additional view fields required by form
		$view->action = 'add';
		$view->buttonText = 'Aggiungi';
		$view->actionTemplate = 'forms/_rubricaForm.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );
	}

	function editAction()
	{
		$view->setScriptPath('/home/workspace/Scout/ScoutPad/application/rubrica/views/scout');
		$view->title = "Modifica membro";
		$staff = new Staff();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			
			$post = Zend_Registry::get('post');
			
			$filter = Zend_Registry::get('filter');
			
			$post = $filter->filter($_POST);
			
			$id = is_int($post['id']) ? $post['id'] : -1;
			$nome = trim($post['nome']);
			$cognome = trim($post['cognome']);
			$mail = trim($post['mail']);
			$cellulare = trim($post['cellulare']);
			$fisso = trim($post['fisso']);
			$gruppo = trim($post['gruppo']);
			$status = trim($post['status']);
			
			
			if ($id !== false) {
				if ($cognome!= '' && $nome != '') {
					$data = array(
							'nome' => $nome,
							'cognome' => $cognome,
							'fisso' => $fisso,
							'gruppo' => $gruppo,
							'cellulare' => $cellulare,
							'mail' => $mail,
							'status' => $status
						);
						$where = 'id = ' . $id;
						$staff->update($data, $where);
						$url = '/rubrica/';
						$this->_redirect($url);
						return;
				} else {
					$view->staff = $staff->find($id)->toArray();
				}
			}

		} else {
			// album id should be $params['id']
			$params = $this->_getAllParams();
			$id = 0;
			if (isset($params['id'])) {
				$id = (int)$params['id'];
			}
			if ($id > 0) {
				$view->staff = $staff->find($id)->toArray();
			}
		}
		// additional view fields required by form
		$view->action = 'edit';
		$view->buttonText = 'Aggiorna';
		$view->actionTemplate = 'forms/_rubricaForm.tpl';
		$this->getResponse()->setBody( $view->render('site.tpl') );

	}

	function deleteAction()
	{
		$view = Zend_Registry::get('view');
		$view->title = "Rimuovi membro";
		$staff = new Staff();
		
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			
			$post = Zend_Registry::get('post');
			
			$filter = Zend_Registry::get('filter');
			
			$id = is_int($post['id']) ? $post['id'] : -1;
			
			if (strtolower($post['del']) == 'yes' && $id > 0) {
				$where = 'id = ' . $id;
				$staff->delete($where);
			}
		} else {
			// album id should be $params['id]
			$params = $this->_getAllParams();
			if (isset($params['id'])) {
				$id = (int)$params['id'];
				if ($id > 0) {
					$view->staff = $staff->find($id)->toArray();
					$view->actionTemplate = 'rubricaDelete.tpl';
					// only render if we have an id.
					$this->getResponse()->setBody( $view->render('site.tpl') );
					return;
				}
			}
		}
		
		// redirect back to the album list in all cases unless we are
		// rendering the template
		$url = '/rubrica/';
		$this->_redirect($url);
	}

	public function noRouteAction()
	{
		$this->_redirect('/');
	}
}

?>
