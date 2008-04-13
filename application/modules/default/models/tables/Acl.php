<?php
	class Acl extends Zend_Db_Table
	{

		
		function insert($data){
			
			$where = array();
			
			foreach( $data as $key => $value ){
				if ( is_null($value) ) $where[] = $key.' IS NULL';
				else $where[] = $key." = '$value'";
			}

			if ( $this->fetchAll($where)->count() > 0 ) { //riga gia presente
				Zend_Registry::get('log')->log('la ACL che si vuole inserire gia\' esiste',Zend_Log::WARN);
				return false;
			} else parent::insert($data);
			
		}
		
		/**
		 * Cerca le ACL di un determinato modulo
		 *
		 * @param string $$module_name nome del modulo
		 * @return Zend_Db_Table_Rowset
		 */
		function getByModulo($module_name){
			
			$sql="SELECT * FROM Acl WHERE Modulo='$module_name' ORDER BY Role ASC, Controller ASC, Action ASC";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
		/**
		 * Cerca le ACL di un determinato gruppo di utenti
		 *
		 * @param string $role_name nome del gruppo di utenti
		 * @return Zend_Db_Table_Rowset
		 */
		function getByRole($role_name){
			
			if ( is_null($role_name) ) {
				$sql="select * from Acl where Role IS NULL order by Modulo ASC, Controller ASC, Action ASC";
			} else $sql="select * from Acl where Role='$role_name' order by Modulo ASC, Controller ASC, Action ASC";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
		/**
		 * Cerca le ACL di un dato modulo per un dato gruppo di utenti
		 *
		 * @param string $module_name nome del modulo
		 * @param string $role_name nome del gruppo di utenti
		 * @return Zend_Db_Table_Rowset
		 */
		function getByModuloAndRole($module_name,$role_name){
			
			$sql="SELECT * FROM Acl WHERE Modulo='$module_name' AND Role='$role_name' ORDER BY Role ASC, Controller ASC, Action ASC";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
		
		
	}
?>