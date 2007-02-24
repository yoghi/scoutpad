<?php
	class Permessi extends Zend_Db_Table
	{

		public function getModuleByName($nome){
			
			$sql="select * from permessi group by modulo order by modulo ASC LIMIT 1";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}

	}

?>
