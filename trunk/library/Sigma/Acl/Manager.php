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
 * @package    Sigma_Acl
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Acl
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 agosto 31 - 11:12 - Stefano Tamagnini  
 */
class Sigma_Acl_Manager {
	
	/**
	 * Sigma_Acl object
	 * @var Sigma_Acl
	 */
	private $acl = null;

	/**
	 * Identificativo dell'utente corrente (0 = guest)
	 *
	 * @var integer 
	 */
	private $user_id = 0;
	
	/**
	 * Roles correnti
	 * @var array
	 */
	private $rolesId = null;	
	
	
	/**
	 * Disable cache
	 * @var boolean
	 */
	private $disable_cache = false;
	
	/**
	 * Modulo corrente
	 * @var integer
	 */
	private $modulo = null;
	
	/**
	 * Classe per semplificare la creazione e storaging delle ACL di un dato "Utente"
	 *
	 * @param integer $user_id identificativo utente 
	 * @param string $role ruolo/i da usare
	 * @param string $modulo modulo da usa
	 * @param string $cache abilito la cache o meno
	 * @throws Zend_Exception
	 */
	public function __construct($user_id,$rolesId,$modulo_name,$cache = true) {
		
		try {
		
			Zend_Loader::loadClass('AclCache','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			Zend_Loader::loadClass('Sigma_Acl');
			
			/*
			$r = explode(",",$rolesId);
			
			foreach ($r as $ruolo){
				$this->roleId[] = $ruolo;
			}
			*/
			$this->rolesId = $rolesId;
			$this->modulo = Sigma_Acl_Manager::getModuleId($modulo_name);
			$this->user_id = $user_id;
			$this->disable_cache = !$cache;
			
		}catch (Zend_Exception $e) {
			throw $e;
		}
		
	}
	
	/**
	 * Carica in memoria le ACL di un dato modulo per un dato richiedente (Role)
	 * 
	 * @return boolean true se il procedimento è andato a buon fine
	 * @throws Zend_Exception
	 */
	public function load(){

			$acl_cache = new AclCache();
			
			//if ( !is_null($this->roleId) ) $where[] = 'Role = '.$acl_cache->getAdapter()->quote($this->roleId);
			//else throw new Zend_Exception('You must specific the role for create Sigma_Acl_Manager');
			
			if ( count($this->rolesId) != 0 ) {
				
				foreach ($this->rolesId as $role){
					$where[] = 'Role = '.$acl_cache->getAdapter()->quote($role);
				}
				
			} else throw new Zend_Exception('You must specific the role/s for create Sigma_Acl_Manager');
			
			
			if ( !is_null($this->modulo) ) $where[] = 'Modulo = '.$acl_cache->getAdapter()->quote($this->modulo);
			else throw new Zend_Exception('You must specific the module for create Sigma_Acl_Manager');
			
			$r = $acl_cache->fetchAll($where)->toArray();
			
			if ( count($r) > 0 && !$this->disable_cache ) {
				
				// E' gia presente in cache
				Zend_Registry::get('log')->log('loading from cache .... ',Zend_Log::DEBUG);
				$this->acl = unserialize( base64_decode($r[0]['Object']) );
				return true;
				
			} else {	
								
				$this->acl = new Sigma_Acl($this->user_id,$this->rolesId,$this->modulo);
				$this->_cacheit();
				

			}

			// in DEFAULT guest deve avere almeno 1 regola!! (Es. Notify)
			if ( $this->acl->count() == 0 && $this->modulo == 'defualt'  ) {
				Zend_Registry::get('log')->log('Non ci sono regole controllare che l\'utente erediti da guest e che guest abbia tutti i diritti fondamentali!',Zend_Log::CRIT);
				return false;	
			}
			
			return true;
	}
	
	/**
	 * Rigenera la cache
	 */
	public function regenCache() {
			
		Zend_Registry::get('log')->log('REGEN CACHE FOR '.$this->rolesId.' LOGIC GROUP: '.$this->logicGroupId.' OVER '.$this->modulo,Zend_Log::DEBUG);
 
		$this->acl = new Sigma_Acl($this->user_id,$this->rolesId,$this->modulo);
		
		$this->_cacheit(true);
	}
	
	/**
	 * Salva in memoria cache le ACL
	 * @param boolean $override sovrascrivi il precedente cache obj 
	 */
	private function _cacheit($override = false){

		$acl_cache = new AclCache();
		$data['User'] = $this->user_id;
		$data['Modulo'] = $this->modulo;
		
		if ( count($this->rolesId) == 0 ){
			$data['Role'] = 1;
		} else {
			$r = '';
			$max = count($this->rolesId);
			for ($i = 0; $i < $max-2; $i++){
				$r .= $this->rolesId[$i].',';
			}
			$r .= $this->rolesId[$max-1];
			$data['Role'] = $r;
		}
		
		if ( $override ) {
			
			//$where[] = $acl_cache->getAdapter()->quoteInto('User = ? ',$this->user_id);
			$where[] = $acl_cache->getAdapter()->quoteInto('Modulo = ? ',$this->modulo);
			$where[] = $acl_cache->getAdapter()->quoteInto('Role = ? ',$data['Role']);
			
			$ris = $acl_cache->delete($where);
			
			if ( $ris > 0 ) Zend_Registry::get('log')->log('cached acl override complete',Zend_Log::DEBUG);
			
		}
		
		Zend_Registry::get('log')->log('cached acl di '. Sigma_Acl_Manager::getRoleName($data['Role']).' per il modulo '.Sigma_Acl_Manager::getModuleName($data['Modulo']),Zend_Log::DEBUG);
		
		if ( $this->acl->count() > 0 ){
			
			$data['Object'] = base64_encode(  serialize( $this->acl ) );
			
			Zend_Registry::get('log')->log('Serializzo oggetto lungo : '. strlen($data['Object']),Zend_Log::NOTICE);
			
			$acl_cache->insert($data);
			
			Zend_Registry::get('log')->log('cached acl obj into db with '.$this->acl->count().' regole',Zend_Log::DEBUG);
			
		} else Zend_Registry::get('log')->log('nessuna regola messa in cache',Zend_Log::WARN);

	}
	
	/**
	 * Return Zend Acl Object
	 * @return Sigma_Acl oggetto per gestire le acl; 
	 */
	public function Acl(){
		return $this->acl;
	}

	/**
	 * Restituisce il nome di un ruolo a partire dal suo id
	 * @param int $roleId identificativo del ruolo
	 */
	public static function getRoleName($roleId){
		
		Zend_Loader::loadClass('AclRole','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		
		$acl_role = new AclRole();
		
		$where[] = 'id = '.$acl_role->getAdapter()->quote($roleId);
		
		$r = $acl_role->fetchAll($where)->toArray();
		
		if ( count($r) > 0 ) {
			return $r[0]['nome'];
		} else return 0;
		
	}
	
	/**
	 * Restituisce l'id di un modulo a partire dal nome
	 * @param string $module_name nome del modulo 
	 */
	public static function getModuleId($module_name){
		
		Zend_Loader::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		
		$module_list = new Modules();
		
		$where[] = 'name = '.$module_list->getAdapter()->quote($module_name);
		
		$r = $module_list->fetchAll($where)->toArray();
		
		if ( count($r) > 0 ) {
			return $r[0]['id'];
		} else return 0;
		
	}
	
	/**
	 * Restituisce il nome di un modulo a partire dall'id
	 * @param string $module_name nome del modulo 
	 */
	public static function getModuleName($moduleId){
		
		Zend_Loader::loadClass('Modules','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		
		$module_list = new Modules();
		
		$where[] = 'id = '.$module_list->getAdapter()->quote($moduleId);
		
		$r = $module_list->fetchAll($where)->toArray();
		
		if ( count($r) > 0 ) {
			return $r[0]['nome'];
		} else return 0;
		
	}
	
}


?>
