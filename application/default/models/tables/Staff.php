<?php
	class Staff extends Zend_Db_Table
	{

		public function insert(&$data){
			
			$status = 0;
			if ( $data['status'] == 'on' || $data['status'] == 1 ) $status = 1;
			$data['status'] = $status;
			
			parent::insert($data);
			
		}
		
		public function update(&$data,$where){
			
			$status = 0;
			if ( $data['status'] == 'on' || $data['status'] == 1 ) $status = 1;
			$data['status'] = $status;
			
			parent::update($data,$where);
			
		}
		
		public function getAttivi() 
		{
			
			$sql="select * from Staff where status=1 order by nome ASC, gruppo DESC ";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}
		
		public function getCollaboratori() 
		{
			
			$sql="select * from Staff where status=0 order by nome ASC, gruppo DESC ";
			
			$s = array(
				'db' => $this->_db,
				'table' => $this ,
				'data' => $this->_db->fetchAll($sql) 
			);
			
			return new Zend_Db_Table_Rowset($s);
		}

	}

?>
