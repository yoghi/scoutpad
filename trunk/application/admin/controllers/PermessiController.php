<?php

class Admin_PermessiController extends Sigma_Controller_Action 
{
	private $acl = null;
	private $modulo = null;
	private $role = 'guest';
	
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
		$this->modulo = $this->getRequest()->getModuleName();
	}
	
	public function indexAction()
	{
		
		$params = $this->_getAllParams();
		
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			
			if ( isset($_POST['modulo']) ) $this->modulo = trim($_POST['modulo']);
			if ( isset($_POST['role']) ) $this->role = trim($_POST['role']);
			$this->_redirect('/admin/permessi/index/modulo/'.$this->modulo.'/role/'.$this->role);
			
		} else {
			if ( isset($params['modulo']) ) $this->modulo = $params['modulo'];
			if ( isset($params['role']) ) $this->role = $params['role'];
		}
		
		
		$this->view->buttonText = 'Search';
		$this->view->title = "Permessi";
		$this->view->actionTemplate = 'contents/permessi.tpl';	
		$this->view->module_name = $this->modulo;
		$this->view->current_role = $this->role;
		
		/*
		 * Moduli
		 */
		
		$module_db = new Modules();
		$rows = $module_db->fetchAllName();
		foreach($rows->toArray() as $r){
			$module_options[$r['nome']] = $r['nome'];
		}
		$this->view->module_options = $module_options;
		
		/*
		 * Roles
		 */
		
		$roles_db = new AclRole();
		$rows = $roles_db->fetchAll();
		foreach($rows->toArray() as $r){
			$role_options[$r['nome']] = $r['nome'];
		}
		$this->view->role_options = $role_options;
		
		
		$acl_db = new Acl();
		$where[] = $acl_db->getAdapter()->quoteInto('Modulo = ?', $this->modulo);
		$where[] = $acl_db->getAdapter()->quoteInto('Role = ? OR Role IS NULL', $this->role );
		$rows = $acl_db->fetchAll($where);
		$this->view->acl_list = $rows->toArray();
		
		
		
		
		
		
		
		$acl = new Zend_Acl();
		/*
		 * TABELLA ACL (Esempio)
		   id 	Modulo 	Controller 	Action 	Role
			1 	default 	index 	NULL 	NULL
			2 	admin 	permessi 	NULL 	NULL
		 */

		
		
		
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	
		
/*		
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
	
	public function addAction(){}
	
	public function changeAction(){}
	
	public function deleteAction(){}

	public function noRouteAction()
	{
		//$this->_redirect('/');
		$this->indexAction();
	}
}

?>