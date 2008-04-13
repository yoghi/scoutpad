<?php
	class AclCache extends Zend_Db_Table
	{

		function insert($data){
			
			$where = array();
			
			foreach( $data as $key => $value ){
				$where[] = $key." = '$value'";
			}

			if ( $this->fetchAll($where)->count() > 0 ) { //riga gia presente
				Zend_Registry::get('log')->log('la ACLCache che si vuole inserire gia\' esiste',Zend_Log::WARN);
				return false;
			} else parent::insert($data);
			
		}
		
	}
?>