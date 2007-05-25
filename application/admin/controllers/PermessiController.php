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
		
		//analizzo il GET URL (per la visualizzazione)  
		$this->_role($filter);
		$this->_action($filter);
		$this->_modulo($filter);
		$this->_controller($filter);
		
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
			$this->view->current_azione = $this->azione;
		} else {
			$this->view->current_azione = '';
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
		if ( count($this->_getAllParams()) == 3 || ( is_null($this->role) && is_null($this->modulo) ) ) {
			
			$this->view->title_acl = 'Elenco ACL applicabili su tutti gli utenti';

			// itero su ogni acl restituita e genero il corretto array
			foreach( $acl_db->getByRole(null)->toArray() as $acl_single){
				
				$modulo = $acl_single['Modulo'];
				$controller = is_null($acl_single['Controller']) ? '*' :  $acl_single['Controller'];
				$azione = is_null($acl_single['Action']) ? '*' :  $acl_single['Action'];
				
				$acl_list['All people'][$acl_single['id']] = array (
							'Modulo' => $modulo,
							'Controller' => $controller,
							'Action' => $azione
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
				$azione = is_null($acl_single['Action']) ? '*' :  $acl_single['Action'];
				
				$acl_list[$this->role][$acl_single['id']] = array (
							'Modulo' => $modulo,
							'Controller' => $controller,
							'Action' => $azione
				); 	
			}
				

		}
		
		$this->view->acl_list = $acl_list;
		
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );

		
	}
	
	private function _modulo(Zend_Filter_Alpha $filter){
		
		if ( isset($this->params['modulo']) ){
			$this->modulo = strtolower($filter->filter($this->params['modulo']));
			Zend_Registry::get('log')->log('Modulo: '.$this->role,Zend_Log::DEBUG);
			if ( $this->modulo == 'all' ) $this->modulo = null;
		} else Zend_Registry::get('log')->log('parametro Modulo mancante',Zend_Log::DEBUG);
		
	}
	
	private function _action(Zend_Filter_Alpha $filter){
		
		if ( isset($this->params['azione']) ){
			$this->azione = strtolower($filter->filter($this->params['azione']));
			Zend_Registry::get('log')->log('Action: '.$this->azione,Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('parametro Action mancante',Zend_Log::DEBUG);
		
	}
	
	private function _controller(Zend_Filter_Alpha $filter){
		
		if ( isset($this->params['risorsa']) ){
			$this->risorsa = strtolower($filter->filter($this->params['risorsa']));
			Zend_Registry::get('log')->log('Risorsa: '.$this->risorsa,Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('parametro Risorsa mancante',Zend_Log::DEBUG);
		
	}
	
	private function _role(Zend_Filter_Alpha $filter){		
		
		if ( isset($this->params['role']) ){
			//$this->role = $filter->filter($this->params['role']);
			$this->role = strtolower($this->params['role']);
			Zend_Registry::get('log')->log('Role: '.$this->role,Zend_Log::DEBUG);
			if ( $this->role == 'all' ) $this->role = null;
		} else Zend_Registry::get('log')->log('parametro Role mancante',Zend_Log::DEBUG);
		
	}
	
	/**
	 * rimuovo un ACL
	 * @todo verificare che non si elimino ACL fondamentali
	 */
	public function removeAction(){
		
		if ( !isset($this->params['id']) ) $this->_redirect('/admin/permessi/');
		if ( '1' == $this->params['id'] ) $this->notify('/admin/permessi/','errore','Non si possono rimuovere le ACL di BASE');
		
		try {
			$acl_db = new Acl();
			$acl_single = $acl_db->find($this->params['id'])->toArray();
			
			if ( count($acl_single) == 0 ) $this->notify('/admin/permessi/','errore','ACL inesistente'); 
			
			$modulo = $acl_single[0]['Modulo'];
			$controller = is_null($acl_single[0]['Controller']) ? '*' :  $acl_single[0]['Controller'];
			$azione = is_null($acl_single[0]['Action']) ? '*' :  $acl_single[0]['Action'];
			
			$stone = $acl_single[0]['stone'];
			
			/**
			 * Stone non è configurabile da utente, ma solo lato programmazione interna!!
			 */
			if ( $stone == 1 ) { //fondamentale, allora devo chiedere la conferma altrimenti, elimino subito
				$this->notify('/admin/permessi/','conferma','Sicuro di voler eliminare questa regola la ACL '.$this->params['id']." : $modulo-&gt;$controller-&gt;$azione",'/admin/permessi/delete/id/'.$this->params['id']);
			} else {
				$this->deleteAction();
			}
			
		} catch ( Exception $e) {
			$this->notify('/admin/permessi/','errore','servizio non disponibile'); 
		}
		
	}
	
	/**
	 * Aggiungi una regola ACL
	 */
	public function addAction(){

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {	
			
			//controllo se ci sono i 4 parametri 

			if (  isset($_POST['form_modulo']) &&  isset($_POST['form_controller']) &&  isset($_POST['form_action']) &&  isset($_POST['form_role'])   ) {
				
					$acl = new Acl();
					
					$modulo = $_POST['form_modulo'] != '' ? strtolower($_POST['form_modulo']) : null;
					$controller = $_POST['form_controller'] != '' ? strtolower($_POST['form_controller']) : null;
					$azione = $_POST['form_action'] != '' ? strtolower($_POST['form_action']) : null;
					$role = $_POST['form_role'] != 'all' ? strtolower($_POST['form_role']) : null;
					
					$date = array(
						'Modulo' => $modulo,
						'Controller' => $controller,
						'Action' => $azione,
						'Role' => $role
					);		
					
					$ret = $acl->insert($date);
					
					if ( $ret === false ) $this->notify('/admin/permessi/','errore','risorsa non disponibile');
					
					$acl_manager = new Sigma_Acl_Manager($role,$modulo);
					$acl_manager->regenCache();
					
					$this->notify('/admin/permessi/','complete','inserimento completato con successo di '.$modulo.' -> '.$controller.' -> '.$azione.' in '.$_POST['form_role']);

			} else {
				
				$this->notify('/admin/permessi/','errore','missing params');
				
			}
			
		}
		
	}
	
	/**
	 * Elimino definitivamente una ACL 
	 * @todo: verificare che non sia marchiata fondamentale
	 */
	public function deleteAction(){
		
		//ricorda che non è possibile eliminare le entry importanti ... (Es. login, notify, default...)
		$id = $this->_getParam('id'); //id dell'ACL
		
		if ( !is_null($id) ) {
			
			if ( !isset($this->params['id']) ) $this->_redirect('/admin/permessi/');
			if ( '1' == $this->params['id'] ) $this->notify('/admin/permessi/','errore','Non si possono rimuovere le ACL di BASE');
			
			try {
			
				$acl_db = new Acl();
				$acl_single = $acl_db->find($this->params['id'])->toArray();
				
				if ( count($acl_single) == 0 ) $this->notify('/admin/permessi/','errore','ACL inesistente'); 
				
				$where = $acl_db->getAdapter()->quoteInto('id = ?',$id);
				$ret = $acl_db->delete($where);
				
				if ( $ret > 0 ) {
					
					$modulo = $acl_single[0]['Modulo'];
					$controller = is_null($acl_single[0]['Controller']) ? '*' :  $acl_single[0]['Controller'];
					$azione = is_null($acl_single[0]['Action']) ? '*' :  $acl_single[0]['Action'];
					$role = is_null($acl_single[0]['Role']) ? '*' :  $acl_single[0]['Role'];
					
					Zend_Registry::get('log')->log('rimossa la ACL '.$id." : $modulo-&gt;$controller-&gt;$azione in $role",Zend_Log::NOTICE);
					
					$acl_manager = new Sigma_Acl_Manager($acl_single[0]['Role'],$acl_single[0]['Modulo']);
					$acl_manager->regenCache();
							
					$this->notify('/admin/permessi/','complete',$id.' ACL eliminata : '."$modulo-&gt;$controller-&gt;$azione in $role"); 
				}
				
			} catch (Exception $e) {
				Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR);
				$this->notify('/admin/permessi/','errore','servizio non disponibile'); 
			}
			
		} else {
			$this->_redirect('/admin/permessi/');
		}
		
	}

	public function noRouteAction()
	{
		//$this->_redirect('/');
		$this->indexAction();
	}
}

?>