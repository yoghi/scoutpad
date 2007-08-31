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
 * @package    Sigma_Plugin
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Plugin
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 agosto 31 - 11:12 - Stefano Tamagnini  
 */
class Sigma_Acl_Manager {
	
	/**
	 * Zend_Acl object
	 * @var Zend_Acl
	 */
	private $acl = null;
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
	 * Numero di regole per modulo
	 * @var int
	 */
	private $num_regole = 0;
	/**
	 * Inherit role
	 * @var array
	 */
	private $other_role = array();
	
	/**
	 * Classe per semplificare la creazione e storaging delle ACL
	 * 
	 * @param string $role ruolo da usare
	 * @param string $modulo modulo da usa
	 * @throws Zend_Exception
	 */
	public function __construct($role,$modulo) {
		
		try {
			Zend_Loader::loadClass('Acl','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			Zend_Loader::loadClass('AclCache','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			Zend_Loader::loadClass('AclRole','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			Zend_Loader::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			
			Zend_Loader::loadClass('Zend_Acl');
			Zend_Loader::loadClass('Zend_Acl_Role');
			Zend_Loader::loadClass('Zend_Acl_Resource');
			$this->role = $role;
			$this->modulo = $modulo;

		}catch (Zend_Exception $e) {
			throw $e;
		}
		
	}
	
	/**
	 * Carica in memoria le ACL di un dato modulo per un dato richiedente (Role)
	 * @throws Zend_Exception
	 */
	public function load(){

			$acl_cache = new AclCache();
		
			if ( !is_null($this->role) ) $where[] = 'Role = '.$acl_cache->getAdapter()->quote($this->role);
			else throw new Zend_Exception('You must specific the role for create Sigma_Acl_Manager');
			
			if ( !is_null($this->modulo) ) $where[] = 'Modulo = '.$acl_cache->getAdapter()->quote($this->modulo);
			else throw new Zend_Exception('You must specific the module for create Sigma_Acl_Manager');
			
			$r = $acl_cache->fetchAll($where)->toArray();
			
			if ( count($r) > 0 ) {
				Zend_Registry::get('log')->log('ACL MANAGER FOR '.$this->role.' OVER '.$this->modulo,Zend_Log::DEBUG);
				Zend_Registry::get('log')->log('loading from cache .... ',Zend_Log::DEBUG);
				$this->acl = unserialize( base64_decode($r[0]['Object']) );
			} else {					
				$this->acl = new Zend_Acl();
				Zend_Registry::get('log')->log('ACL MANAGER FOR '.$this->role.' OVER '.$this->modulo,Zend_Log::DEBUG);
				$this->_addInheritRole($this->role);
				$this->_addRules();
				$this->_cacheit();
			}
			
	}
	
	/**
	 * Carica in memoria le ACL di un dato modulo per un dato richiedente (Role) prendendo i dati dalla cache
	 * @return boolean true se il procedimento è andato a buon fine
	 * @throws Zend_Exception
	 */
	public function loadFromCache(){
		
		$acl_cache = new AclCache();
			
		if ( !is_null($this->role) ) $where[] = 'Role = '.$acl_cache->getAdapter()->quote($this->role);
		else throw new Zend_Exception('You must specific the role for create Sigma_Acl_Manager');
		
		if ( !is_null($this->modulo) ) $where[] = 'Modulo = '.$acl_cache->getAdapter()->quote($this->modulo);
		else throw new Zend_Exception('You must specific the module for create Sigma_Acl_Manager');
		
		$r = $acl_cache->fetchAll($where)->toArray();
		
		if ( count($r) > 0 ) {
			Zend_Registry::get('log')->log('ACL MANAGER FOR '.$this->role.' OVER '.$this->modulo,Zend_Log::DEBUG);
			Zend_Registry::get('log')->log('loading from cache .... ',Zend_Log::DEBUG);
			$this->acl = unserialize( base64_decode($r[0]['Object']) );
			return true;
		} 
		
		return false;
		
	}
	
	/**
	 * Rigenera la cache
	 */
	public function regenCache() {
		$this->acl = new Zend_Acl();
		$role = is_null($this->role) ? 'guest' : $this->role;
		Zend_Registry::get('log')->log('REGEN CACHE FOR '.$role.' OVER '.$this->modulo,Zend_Log::DEBUG);
		$this->_addInheritRole($this->role);
		$this->_addRules();
		$this->_cacheit(true);
	}
	
	/**
	 * Salva in memoria cache le ACL
	 * @param boolean $override sovrascrivi il precedente cache obj 
	 */
	private function _cacheit($override = false){

		$acl_cache = new AclCache();
		
		$data['Modulo'] = $this->modulo;
		$data['Role'] = is_null($this->role) ? 'guest' : $this->role;
		
		if ( $override ) {
			
			$where[] = $acl_cache->getAdapter()->quoteInto('Modulo = ? ',$this->modulo);
			$where[] = $acl_cache->getAdapter()->quoteInto('Role = ? ',$data['Role']);
			
			$ris = $acl_cache->delete($where);
			
			if ( $ris > 0 ) Zend_Registry::get('log')->log('cached acl override complete',Zend_Log::DEBUG);
			
		}
		
		Zend_Registry::get('log')->log('cached acl di '.$data['Role'].' per il modulo '.$data['Modulo'],Zend_Log::DEBUG);
		
		if ( $this->num_regole > 0 ){
			$data['Object'] = base64_encode(  serialize( $this->acl ) );
			$acl_cache->insert($data);
			Zend_Registry::get('log')->log('cached acl obj into db with '.$this->num_regole.' regole',Zend_Log::DEBUG);
		} else Zend_Registry::get('log')->log('nessuna regola messa in cache',Zend_Log::WARN);

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
		
		$this->acl->addRole( new Zend_Acl_Role($ris[0]['nome']) , $ris[0]['inherit'] );
		
		Zend_Registry::get('log')->log('add role : '.$ris[0]['nome'],Zend_Log::DEBUG);
	
	}

	/**
	 * Aggiunge le regole ACL 
	 */
	private function _addRules(){

		foreach( array_reverse($this->other_role) as $role){	
			$this->_addRule($role,$this->modulo);
		}
		
		// aggingo le ACL del ROLE corrente
		$this->_addRule($this->role,$this->modulo);
		
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
			
			if ( !$this->acl->has($acl_single['Controller']) ){
				$this->acl->add( new Zend_Acl_Resource($acl_single['Controller']) );
			}
			
			$this->acl->allow($role,$acl_single['Controller'],$acl_single['Action']);
			
			$log->log("allow($role_name,".$acl_single['Controller'].",".$acl_single['Action'].")",Zend_Log::DEBUG);

		}

		$this->num_regole = $this->num_regole + count($regole);
		
	}
	
	/**
	 * Return Zend Acl Object
	 * @return Zend_Acl oggetto per gestire le acl; 
	 */
	public function getAcl(){
		return $this->acl;
	}

}


?>
