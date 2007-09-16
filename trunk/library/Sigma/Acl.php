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
 * @package    Sigma
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 agosto 31 - 11:12 - Stefano Tamagnini  
 */
class Sigma_Acl extends Zend_Acl {
	
	/**
	 * Gestore dei permessi
	 * @var Sigma_Acl_Permission
	 */
	private $permission = null;
	
	/**
	 * Identificativo dell'utente corrente (0 = guest)
	 *
	 * @var integer 
	 */
	private $user_id = 0;
	
	/**
	 * Role corrente
	 * @var string
	 */
	private $role = null;
	
	/**
	 * Modulo corrente
	 * @var string
	 */
	private $modulo = null;
	
	/**
	 * Inherit role
	 * @var array
	 */
	private $other_role = array();

	/**
	 * Numero di regole per modulo
	 * @var int
	 */
	private $num_regole = 0;
	
	/**
	 * Costruttore Sigma_Acl
	 *
	 * @param integer $user_id identificativo dell'utente
	 */
	public function __construct($user_id,$role,$modulo){
		
		$this->role = $role;
		$this->modulo = $modulo;
		$this->user_id = $user_id;
		
		//database
		Zend_Loader::loadClass('Acl','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		Zend_Loader::loadClass('AclUser','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		Zend_Loader::loadClass('AclRole','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		Zend_Loader::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		
		//class
		Zend_Loader::loadClass('Sigma_Acl_Permission');
		
		Zend_Registry::get('log')->log('Creo ACL per '.$this->role.' nel modulo '.$this->modulo,Zend_Log::DEBUG);
		
		$this->permission = new Sigma_Acl_Permission();
		$this->_addInheritRole($this->role);
		$this->_addRules();
		
	}
	
	/**
	 * Conto il numero di regole presenti
	 * 
	 */
	public function count(){
		return $this->num_regole;
	}
	
	/**
	 * Ho il permesso Sigma_Acl_Permission::<zzz> ?
	 *
	 * @param string $controller il controller su cui controllare
	 * @param char $permesso che permesso desidero
	 * @return boolean 
	 */
	public function hasPermission($controller,$permesso){
		return $this->permission->hasPermission($this->user_id,$this->modulo,$controller,$permesso);
	}
	
	/**
	 * Aggiunge un i richiedenti (Role) in modo da considerare anche gli inherits
	 * @param string $role richiedente
	 */
	private function _addInheritRole($role) {

		if ( $role === null ) {
			// vuole dire che è un utente NULL => di fatto GUEST!
			$role = 'guest';
		}
		 
		$acl_role = new AclRole();
		
		$where = 'nome = '. $acl_role->getAdapter()->quote($role);
		
		$ris = $acl_role->fetchAll($where)->toArray();
		
		if ( count($ris) == 0 ) {
			Zend_Registry::get('log')->log('ruolo inesistente: '.$role,Zend_Log::ERR);
			return;
		}
		
		Zend_Registry::get('log')->log('cerco di creare il ruolo di '.$role,Zend_Log::DEBUG);
		
		if ( !is_null($ris[0]['inherit']) ) {
			$this->other_role[] = $ris[0]['inherit'];
			// devo verificare che anche lui non abbia altri inherit!!
			$this->_addInheritRole($ris[0]['inherit']);
		} 
		
		$this->addRole( new Zend_Acl_Role($ris[0]['nome']) , $ris[0]['inherit'] );
		
		Zend_Registry::get('log')->log('add role : '.$ris[0]['nome'],Zend_Log::DEBUG);
	
	}
	
	/**
	 * Aggiunge le regole ACL 
	 * 
	 */
	private function _addRules(){

		foreach( array_reverse($this->other_role) as $role){	
			$this->_addRule($role,$this->modulo);
		}

		// aggingo le ACL del ROLE corrente
		$this->_addRule($this->role,$this->modulo);
		
		//aggiungo le ACL dell'utente corrente
		$this->_addUserRule();
		
	}
	
	/**
	 * Aggiungo le regole ACL dell'utente corrente
	 * 
	 */
	private function _addUserRule(){
		//$this->user_id;
		$log = Zend_Registry::get('log');
		$log->log('cerco di aggiungere la regola per '.$this->user_id.' nel modulo '.$this->modulo ,Zend_Log::DEBUG);
		
		$acl_db = new AclUser();
		
		$where = array();
		$where[] = 'User = '.$acl_db->getAdapter()->quote($this->user_id);
		$where[] = 'Modulo = '.$acl_db->getAdapter()->quote($this->modulo);
		
		$regole = $acl_db->fetchAll($where)->toArray();
		
		foreach($regole  as $acl_single){
			
			$modulo_s = $acl_single['Modulo'];
			$controller_s = is_null($acl_single['Controller']) ? '*' :  $acl_single['Controller'];
			$action_s = is_null($acl_single['Action']) ? '*' :  $acl_single['Action'];
			
			$acl_list[$this->role][$acl_single['id']] = array (
						'Modulo' => $modulo_s,
						'Controller' => $controller_s,
						'Action' => $action_s
			);
			
			if ( !$this->has($acl_single['Controller']) ){
				$this->add( new Zend_Acl_Resource($acl_single['Controller']) );
			}
			
			$this->allow($this->role,$acl_single['Controller'],$acl_single['Action']);
			
			$role_name = is_null($this->role) ? 'guest' : $this->role;
			
			$log->log("allow($role_name,".$acl_single['Controller'].",".$acl_single['Action'].")",Zend_Log::DEBUG);

		}

		$this->num_regole = $this->num_regole + count($regole);
		
	}
	
	/**
	 * Aggiunge solo le regole di un dato richiedente su un dato modulo
	 * @param string $role richiedente
	 * @param string $modulo modulo
	 */
	private function _addRule($role,$modulo){
		
		$log = Zend_Registry::get('log');
		
		$role_name = is_null($role) ? 'guest' : $role;
		
		$log->log('cerco di aggiungere la regola per '.$role_name.' nel modulo '.$modulo ,Zend_Log::DEBUG);
		
		$acl_db = new Acl();
		$where = array();
		$where[] = 'Modulo = '.$acl_db->getAdapter()->quote($modulo);
		
		if ( !is_null($role) && $role != 'guest'  ) $where[] = 'Role = '.$acl_db->getAdapter()->quote($role);
		else $where[] = 'Role IS NULL';
		
		// itero su ogni acl restituita e genero il corretto array

		$regole = $acl_db->fetchAll($where)->toArray();
		
		foreach($regole  as $acl_single){
			
			$modulo_s = $acl_single['Modulo'];
			$controller_s = is_null($acl_single['Controller']) ? '*' :  $acl_single['Controller'];
			$action_s = is_null($acl_single['Action']) ? '*' :  $acl_single['Action'];
			
			$acl_list[$role][$acl_single['id']] = array (
						'Modulo' => $modulo_s,
						'Controller' => $controller_s,
						'Action' => $action_s
			);
			
			if ( !$this->has($acl_single['Controller']) ){
				$this->add( new Zend_Acl_Resource($acl_single['Controller']) );
			}
			
			$this->allow($role,$acl_single['Controller'],$acl_single['Action']);
			
			$log->log("allow($role_name,".$acl_single['Controller'].",".$acl_single['Action'].")",Zend_Log::DEBUG);

		}

		$this->num_regole = $this->num_regole + count($regole);
		
	}
	
	/**
	 * Gestisco la serializzazione
	 * nota bene come debbano essere inseriti anche i campi ereditati! Non metto Sigma_Acl_Permission in quanto non contiene dati importanti
	 */
	function __sleep(){
		return array('user_id','role','modulo','num_regole','_roleRegistry','_resources','_rules');
	}
	
	/**
	 * Gestisco la serializzazione
	 * Attenzione che non c'è l'autoload delle classi mancanti
	 */
	function __wakeup(){
		Zend_Loader::loadClass('Sigma_Acl_Permission');
		$this->permission = new Sigma_Acl_Permission();
	}
	
}

?>