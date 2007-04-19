<?php


	class Sigma_Acl_Manager {
		
		private $acl = null;
		private $role = null;
		private $modulo = null;
		private $num_regole = 0;
		// inherit role
		private $other_role = array();
		
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

			}
			catch (Zend_Exception $e) {
				throw $e;
			}
			
		}
		
		public function load(){
			
				$acl_cache = new AclCache();
				
				if ( !is_null($this->role) ) $where[] = 'Role = '.$acl_cache->getAdapter()->quote($this->role);
				else throw new Exception('You must specific the role for create Sigma_Acl_Manager',E_USER_ERROR);
				
				if ( !is_null($this->modulo) ) $where[] = 'Modulo = '.$acl_cache->getAdapter()->quote($this->modulo);
				else throw new Exception('You must specific the module for create Sigma_Acl_Manager',E_USER_ERROR);
				
				$r = $acl_cache->fetchAll($where)->toArray();
				
				if ( count($r) > 0 ) {
					Zend_Registry::get('log')->log('ACL MANAGER FOR '.$this->role.' OVER '.$this->modulo,Zend_Log::DEBUG);
					Zend_Registry::get('log')->log('loading from cache .... ',Zend_Log::DEBUG);
					$this->acl = unserialize( base64_decode($r[0]['Object']) );
				} else {					
					$this->acl = new Zend_Acl();
					Zend_Registry::get('log')->log('ACL MANAGER FOR '.$this->role.' OVER '.$this->modulo,Zend_Log::DEBUG);
					$this->_addRole();
					$this->_addRules();
					$this->_cacheit();
				}
				
		}
		
		public function loadFromCache(){
			
			$acl_cache = new AclCache();
				
			if ( !is_null($this->role) ) $where[] = 'Role = '.$acl_cache->getAdapter()->quote($this->role);
			else throw new Exception('You must specific the role for create Sigma_Acl_Manager',E_USER_ERROR);
			
			if ( !is_null($this->modulo) ) $where[] = 'Modulo = '.$acl_cache->getAdapter()->quote($this->modulo);
			else throw new Exception('You must specific the module for create Sigma_Acl_Manager',E_USER_ERROR);
			
			$r = $acl_cache->fetchAll($where)->toArray();
			
			if ( count($r) > 0 ) {
				Zend_Registry::get('log')->log('ACL MANAGER FOR '.$this->role.' OVER '.$this->modulo,Zend_Log::DEBUG);
				Zend_Registry::get('log')->log('loading from cache .... ',Zend_Log::DEBUG);
				$this->acl = unserialize( base64_decode($r[0]['Object']) );
				return true;
			} 
			
			return false;
			
		}
		
		private function _cacheit(){

			$acl_cache = new AclCache();
			$data['Modulo'] = $this->modulo;
			$data['Role'] = $this->role;
			
			if ( $this->num_regole > 0 ){
				$data['Object'] = base64_encode(  serialize( $this->acl ) );
				$acl_cache->insert($data);
				Zend_Registry::get('log')->log('cached acl obj into db with '.$this->num_regole.' regole',Zend_Log::DEBUG);
			}

		}
		
		private function _addRole($role = null) {
			
			 /**
			 * Devo calcolare anche gli inherits
			 */
			if ( $role === null ) $role = $this->role;
			 
			$acl_role = new AclRole();
			
			$where = 'nome = '. $acl_role->getAdapter()->quote($role);
			
			$ris = $acl_role->fetchAll($where)->toArray();
			
			Zend_Registry::get('log')->log('cerco di creare il ruolo di '.$role,Zend_Log::DEBUG);
			
			if ( !is_null($ris[0]['inherit']) ) {
				$this->other_role[] = $ris[0]['inherit'];
				$this->_addRole($ris[0]['inherit']);
			} 
			
			$this->acl->addRole( new Zend_Acl_Role($ris[0]['nome']) , $ris[0]['inherit'] );
			
			Zend_Registry::get('log')->log('add role : '.$ris[0]['nome'],Zend_Log::DEBUG);
		
		}

		private function _addRules(){

			foreach( array_reverse($this->other_role) as $role){	
				$this->_addRule($role,$this->modulo);
			}
			
			$this->_addRule($this->role,$this->modulo);
			
		}
		
		private function _addRule($role,$modulo){
			
			$log = Zend_Registry::get('log');
			
			$log->log('cerco di aggiungere la regola per '.$role.' nel modulo '.$modulo ,Zend_Log::DEBUG);
			
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
				
				$log->log("allow($role,".$acl_single['Controller'].",".$acl_single['Action'].")",Zend_Log::DEBUG);
	
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