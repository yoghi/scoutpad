<?php
	class Modules extends Zend_Db_Table
	{

		public function getModuleByName($nome){
			
			$sql="select * from modules where nome='$nome' order by nome ASC LIMIT 1 ";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
		public function getAttivi() 
		{
			
			$sql="select * from modules where status=1 order by nome ASC, gruppo DESC ";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}

	}

?>
