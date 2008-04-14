<?php

/**
 * Classe per memorizzare i dati in un database
 */
class Sigma_Flow_Storage_Database implements Sigma_Flow_Storage_Interface {
	
	
	/**
	 * Default table name 
	 *
	 */
	const TABLENAME = 'Token';
	
	/**
	 * Name of current table
	 *
	 * @var string
	 */
	private $tablename; 
	
	
	/**
	 * Last token inserted!
	 */
	private $last;
	
	
	/**
	 * Costruttore per salvare i dati flow (Flow,Token) in un database
	 *
	 * @param string $member campo obbligatorio per definire l'indice da usare 
	 * @param string $tablename
	 * @throws Sigma_Flow_Storage_Exception If tablename not supported
	 */
	public function __construct($tablename = self::TABLENAME ) {
		
		try {
			
			Zend_Loader::loadClass(ucfirst($tablename) ,BASE_DIRECTORY.'/application/modules/default/models/tables/');
			$this->tablename = ucfirst($tablename); //qui ci sono se la load è andata a buon fine!!
			
		} catch (Zend_Exception $e) {
			Zend_Registry::get('log')->log('Non posso accedere alla tabella dei token',Zend_Log::ERR );
			throw new Sigma_Flow_Storage_Exception('Impossibile accedere alla tabella corretta');
		}

	}
	
	/**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @param  mixed $contents , chiave => valore corrisponde a colonna => valore
     * @throws Sigma_Flow_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents){
		
		try {
			
			$obj = $this->tablename."";
			$token = new $obj();
			$token->insert($contents);
			
		}catch( Zend_Exception $e){
			Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR ); //registro l'errore e propago l'ecezzione
			throw new Sigma_Flow_Storage_Exception('Problema interno');
		}
		
	}
	
	/**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */
	public function read($find){
		
	 	try {
	 		
			$obj = $this->tablename."";
			$token = new $obj();
			$data = $token->find($find); //Zend_Db_Table_Rowset
			
		}catch( Zend_Exception $e){
			Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR ); //registro l'errore e propago l'ecezzione
			throw $e;
		}
		
		if ( $data->count() == 0 ) return null; //non ho trovato la notifica
		
		$d = $data->toArray();
		
		$ret = array();
		$ret['info'] = unserialize(base64_decode($d[0]['info']));
		$ret['url'] = $d[0]['uri']; 

		return $ret;
		
	}
	
	/**
     * Defined by Sigma_Flow_Storage_Interface
     * Inutile dentro un db dove i token non sono divisi per utente!
     *
     * @throws Sigma_Flow_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
	public function clear(){
		$this->clearAll();
	}
	
	/**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
	public function clearAll(){
		
		try {
			
			$obj = $this->tablename."";
			$token = new $obj();
			$token->delete('true');
			
		} catch( Zend_Exception $e){
			Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR ); //registro l'errore e propago l'ecezzione
			throw $e;
		}
		
	}
	
	/**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If it is impossible to determine whether storage is empty
     * @return boolean
     */
	public function isEmpty(){
		
		try {
	 		
			$obj = $this->tablename."";
			$token = new $obj();
			$data = $token->find('*'); //Zend_Db_Table_Rowset
			
		}catch( Zend_Exception $e){
			Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR ); //registro l'errore e propago l'ecezzione
			throw $e;
		}
		
		if ( $data->count() == 0 ) return true;
		
		return false;
		
	}
	
}

?>