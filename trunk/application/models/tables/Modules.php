<?php
	class Modules extends Zend_Db_Table
	{
		/**
		 * Restituisce tutti i nomi dei moduli attivi
		 *
		 * @return Zend_Db_Table_Rowset
		 */
		function fetchAllActive(){
			
			$sql="select * from Modules where status=1 order by name ASC";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
	}
?>