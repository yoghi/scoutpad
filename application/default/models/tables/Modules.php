<?php
	class Modules extends Zend_Db_Table
	{
		
		function fetchAllName(){
			
			$sql="select nome from Modules where status=1 order by nome ASC";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
	}
?>