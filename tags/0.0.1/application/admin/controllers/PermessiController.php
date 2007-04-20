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
class Admin_PermessiController extends Sigma_Controller_Action 
{
	private $acl = null;
	private $modulo = null;		//lavoro su tutti i moduli
	private $risorsa = null; //lavoro su tutti i controller
	private $azione = null;		//lavoro su tutte le action
	private $role = null;		//lavoro su tutti i role
	
	function init()
	{
		Zend_Loader::loadClass('Zend_Acl');
		Zend_Loader::loadClass('Zend_Acl_Role');
		Zend_Loader::loadClass('Zend_Acl_Resource');
		
		try {
			Zend_Loader::loadClass('Acl','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			Zend_Loader::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			Zend_Loader::loadClass('AclRole','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}
		
		Zend_Loader::loadClass('Zend_Filter_Alpha');
		$filter = new Zend_Filter_Alpha();
		$this->_role($filter);
		$this->_action($filter);
		$this->_modulo($filter);
		$this->_controller($filter);
		
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			
			$url = '/admin/permessi/index';
			if ( isset($_POST['role']) && 'all' != $_POST['role'] )  $url .= '/role/'.$filter->filter($_POST['role']);
			if ( isset($_POST['modulo']) && 'all' != $_POST['modulo'] ) $url .= '/modulo/'.$filter->filter($_POST['modulo']);	 
			$this->_redirect($url);
			
		}
		
	}
	
	public function indexAction()
	{
		
		$this->view->buttonText = 'Search';
		$this->view->title = "Permessi";
		$this->view->actionTemplate = 'contents/permessi.tpl';
			
		if ( !is_null($this->modulo) ) {
			$this->view->current_modulo = $this->modulo;
		} else {
			$this->view->current_modulo = '';
		}
		
		if ( !is_null($this->risorsa) ) {
			$this->view->current_controller = $this->risorsa;
		} else {
			$this->view->current_controller = '';
		}
		
		if ( !is_null($this->azione) ) {
			$this->view->current_action = $this->azione;
		} else {
			$this->view->current_action = '';
		}
		
		if ( !is_null($this->role) ) {
			$this->view->current_role = $this->role;
		} else {
			$this->view->current_role = '';
		}
		
		/*
		 * Moduli
		 * Uso i moduli nel db perchè alcuni magari potrebbero essere disattivati
		 */
		
		$module_db = new Modules();
		$elenco_moduli = $module_db->fetchAllName();
		$module_options['all'] = '---';
		foreach($elenco_moduli->toArray() as $r){
			$module_options[$r['nome']] = $r['nome'];
		}
		$this->view->module_options = $module_options;
		
		/*
		 * Roles
		 */
		
		$roles_db = new AclRole();
		$elenco_roles = $roles_db->fetchAll();
		$role_options['all'] = '---';
		foreach($elenco_roles->toArray() as $r){
			$role_options[$r['nome']] = $r['nome'];
		}
		$this->view->role_options = $role_options;
		
		
		
		/**
		 * Acl
			TABELLA ACL (Esempio)
			   id 	Modulo 	Controller 	Action 	Role
				1 	default 	index 	NULL 	NULL
				2 	admin 	permessi 	NULL 	NULL 
		 */
		$acl_db = new Acl();
		
		$acl_list = array();
		
		//non ho settato ne role ne moduli quindi voglio le regole che valgono per tutti e su tutti i moduli
		if ( count($this->_getAllParams()) == 3 ) {
			
			$this->view->title_acl = 'Elenco ACL applicabili su tutti gli utenti';

			// itero su ogni acl restituita e genero il corretto array
			foreach( $acl_db->getByRole(null)->toArray() as $acl_single){
				
				$modulo = $acl_single['Modulo'];
				$controller = is_null($acl_single['Controller']) ? '*' :  $acl_single['Controller'];
				$action = is_null($acl_single['Action']) ? '*' :  $acl_single['Action'];
				
				$acl_list['All people'][$acl_single['id']] = array (
							'Modulo' => $modulo,
							'Controller' => $controller,
							'Action' => $action
				); 	
			}

			
		} else {

			if ( !is_null($this->role) && !is_null($this->modulo)  ) $this->view->title_acl = 'Elenco ACL applicabili sull\'utente '.ucfirst($this->role).' nel modulo '.ucfirst($this->modulo);
			else if ( !is_null($this->role) && is_null($this->modulo)  ) $this->view->title_acl = 'Elenco ACL applicabili sull\'utente '.ucfirst($this->role);
			else if ( is_null($this->role) && !is_null($this->modulo)  ) $this->view->title_acl = 'Elenco ACL del modulo '.ucfirst($this->modulo);
			else $this->view->title_acl = 'Elenco ACL';
	
			$where = array();
			if ( !is_null($this->modulo) ) $where[] = 'Modulo = '.$acl_db->getAdapter()->quote($this->modulo);
			if ( !is_null($this->role) ) $where[] = 'Role = '.$acl_db->getAdapter()->quote($this->role);
			if ( !is_null($this->risorsa) ) $where[] = 'Controller = '.$acl_db->getAdapter()->quote($this->risorsa);
			if ( !is_null($this->azione) ) $where[] = 'Action = '.$acl_db->getAdapter()->quote($this->azione);  
			
			$ris = $acl_db->fetchAll($where); 
			//getByRole($this->role);
			
			// itero su ogni acl restituita e genero il corretto array
			foreach( $ris->toArray() as $acl_single){
				
				$modulo = $acl_single['Modulo'];
				$controller = is_null($acl_single['Controller']) ? '*' :  $acl_single['Controller'];
				$action = is_null($acl_single['Action']) ? '*' :  $acl_single['Action'];
				
				$acl_list[$this->role][$acl_single['id']] = array (
							'Modulo' => $modulo,
							'Controller' => $controller,
							'Action' => $action
				); 	
			}
				

		}
		
		$this->view->acl_list = $acl_list;
		
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
		
		
		/*
		
			Array
			(
			    [admin] => Array
			        (
			            [0] => Array
			                (
			                    [id] => 3
			                    [Modulo] => admin
			                    [Controller] => permessi
			                    [Action] => change
			                    [Role] => member
			                )
			
			            [1] => Array
			                (
			                    [id] => 2
			                    [Modulo] => admin
			                    [Controller] => permessi
			                    [Action] => index
			                    [Role] => 
			                )
			
			        )
			
			    [default] => Array
			        (
			            [0] => Array
			                (
			                    [id] => 1
			                    [Modulo] => default
			                    [Controller] => index
			                    [Action] => 
			                    [Role] => 
			                )
			
			        )
			}

		//$acl = new Zend_Acl();

		$roleGuest = new Zend_Acl_Role('guest');
		$acl->addRole($roleGuest);
		echo '<pre>';
		
		foreach($rows->toArray() as $e ){
			
			if ( !is_null($e['Modulo'])  ){
				
				// Se sono qui allora dovro permettere di accedere solo alle risorse su cui ho esplicitato una politica, altrimenti ACL_DENIED!

				if ( !is_null($e['Controller'])  ){
					
					if ( !$acl->has($e['Controller']) ) {
						//allora la risorsa va aggiunta
						$acl->add(new Zend_Acl_Resource($e['Controller']));
					}
					
					if ( !is_null($e['Action'])  ){
						$acl->allow($roleGuest, $e['Controller'], $e['Action']);
					} else {
						echo 'posso tutte le azioni sul controller : '.$e['Controller'].'<br/>';
						$acl->allow($roleGuest, $e['Controller'], null);
					}
					
					
				} else {
					// tutto possono tutto
					$acl->allow($roleGuest, null, null);
					echo 'posso tutto';
				}
			} else {
				// tutto possono tutto
				$acl->allow($roleGuest, null, null);
				echo 'posso tutto';
			}
			
		}
		
		echo '</pre>';
		
		echo 'Posso fare tutto su Permessi? '; 
		echo $acl->isAllowed('guest', 'permessi', null) ? "allowed" : "denied"; echo '<br/>';
		
		echo 'Posso fare tutto su Index? '; 
		echo $acl->isAllowed('guest', 'index', null) ? "allowed" : "denied"; echo '<br/>';
		
		*/
		
		
	}
	
	private function _modulo(Zend_Filter_Alpha $filter){
		if ( isset($this->params['modulo']) ){
			$this->modulo = $filter->filter($this->params['modulo']);
			Zend_Registry::get('log')->log('Modulo: '.$this->role,Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('parametro Modulo mancante',Zend_Log::DEBUG);
	}
	
	private function _action(Zend_Filter_Alpha $filter){
		if ( isset($this->params['azione']) ){
			$this->azione = $filter->filter($this->params['azione']);
			Zend_Registry::get('log')->log('Action: '.$this->azione,Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('parametro Action mancante',Zend_Log::DEBUG);
	}
	
	private function _controller(Zend_Filter_Alpha $filter){
		if ( isset($this->params['risorsa']) ){
			$this->risorsa = $filter->filter($this->params['risorsa']);
			Zend_Registry::get('log')->log('Risorsa: '.$this->risorsa,Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('parametro Risorsa mancante',Zend_Log::DEBUG);
	}
	
	private function _role(Zend_Filter_Alpha $filter){		
		if ( isset($this->params['role']) ){
			//$this->role = $filter->filter($this->params['role']);
			$this->role = $this->params['role'];
			Zend_Registry::get('log')->log('Role: '.$this->role,Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('parametro Role mancante',Zend_Log::DEBUG);
	}
	
	public function removeAction(){
		
		$auth_module = Zend_Registry::get('auth_module');
		$session = $auth_module->getStorage();
	
		require_once 'Zend/Session/Namespace.php';
    	$namespace = new Zend_Session_Namespace('Zend_Auth');
		var_dump($namespace->storage);
		
		
		
		$this->view->title = "Conferma rimozione regola ACL";
		
		if ( !isset($this->params['id']) ) $this->_redirect('/admin/permessi/');
		if ( '1' == $this->params['id'] ) $this->_redirect('/errore/invalid/');
		
		$acl_db = new Acl();
		
		$acl_single = $acl_db->find($this->params['id'])->toArray();
		
		$modulo = $acl_single[0]['Modulo'];
		$controller = is_null($acl_single[0]['Controller']) ? '*' :  $acl_single[0]['Controller'];
		$action = is_null($acl_single[0]['Action']) ? '*' :  $acl_single[0]['Action'];
		
		$this->view->testo_conferma = "Sicuro di voler eliminare questa regola ACL : ";
		$this->view->errore = $this->params['id'].") $modulo-&gt;$controller-&gt;$action";
		
		$this->view->confirm_uri = '/admin/permessi/delete/';
		
		$this->view->actionTemplate = 'contents/confirm.tpl';
		$this->getResponse()->setBody( $this->view->render('site.tpl') );
	}
	
	public function addAction(){}
	
	public function changeAction(){}
	
	public function deleteAction(){
		//ricorda che non è possibile eliminare la entri 1... (login)
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			
		
		
		}
	}

	public function noRouteAction()
	{
		//$this->_redirect('/');
		$this->indexAction();
	}
}

?>