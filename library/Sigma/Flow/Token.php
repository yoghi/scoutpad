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
 * @package    Sigma_Flow
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Flow
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 maggio 13 - 19:00 - Stefano Tamagnini  
 */
class Sigma_Flow_Token {
	
	/**
     * Singleton instance
     *
     * @var Sigma_Form_Help
     */
    protected static $_instance = null;
    
    /**
     * Session Namespace for control Session 'Sigma'
     * 
     * @var Zend_Session_Namespace 
     */
    protected $flow = null;
    
    /**
     * Lenght of token used into forms 
     *
     * @var int
     */
    private $lenght = 8;
	
    /**
     * Returns an instance of Sigma_Form_Help
     *
     * Singleton pattern implementation
     *
     * @return Sigma_Flow_Token Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
	
    /**
     * Classe adibita alla gestione dei Form durante una sessione di lavoro di un utente
     * Features:
     * 		*) one-use form
     * 		*) ip check
     *
     */
	private function __construct(){
		
		Zend_Loader::loadClass('Token','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
		
		$this->flow = new Zend_Session_Namespace('Sigma_Flow');
		//$this->flo->before_page = $sigma_flow->last_page;	// è la pagina visitata precedentemente
		//$this->flo->last_page = $_SERVER["REQUEST_URI"];	// è la pagina corrente (ossia l'ultima)
		//$_SERVER['HTTP_REFERER']  pagina da cui provengo! 

	}
	
	/**
	 * Inserisce un token in memoria
	 *
	 * @param string $reference l'url di riferimento sorgente (da dove provengo)
	 * @param Array $info l'informazione da memorizzare
	 * 
	 * @return string token code 
	 * @throws Exception
	 */
	public function insert($reference,array $info){
		
		$info_s = base64_encode( serialize($info) ); //in caso di dati complessi va serializzato
		
		$data = array(
			'token' => $this->randToken(),
			'uri' => $reference,
			'info' => $info_s,
			'time' => 'NOW()'
		);
		
		try {
			
			$token = new Token();
			$token->insert($data);
			
		}catch(Zend_Db_Adapter_Exception $e){
			
			if ( substr($e->getCode(),0,2) == '23' ) { //riprovo...potrebbe essere stato solo un problema di token
				
				try {
					$data['token'] = $this->randToken();
					Zend_Registry::get('log')->log('Provo un diverso token',Zend_Log::WARN );
					$token->insert($data);
				} catch (Exception $e){
					throw $e;
				}
				
			}
			
		}catch( Zend_Exception $e){
			Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR ); //registro l'errore e propago l'ecezzione
			throw $e;
		}
		
		return $data['token'];
	}
	
	
	/**
	 * Restituisce il contenuto del Token
	 * @param string $token il token da cercare
	 * @return Zend_Db_Table_Rowset
	 */
	public function getTokenContent($token_id){
		
		try {
			
			$token = new Token();
			$data = $token->find($token_id); //Zend_Db_Table_Rowset
			
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
	 * Un token ha vita limita, bisogna eliminarlo dopo l'uso
	 * @param string $token_id il token da eliminare
	 */
	public function delete($token_id){
		
		$count = 0;
		
		try {
			
			$token = new Token();
			
			$where = $token->getAdapter()->quoteInto('token = ?',$token_id);
			
			$count = $token->delete($where);
			
			if ( $count === 0 ) {
				Zend_Registry::get('log')->log('Non ho potuto eliminare il token utilizzato',Zend_Log::ERR ); 
			}
			
		}catch( Zend_Exception $e){
			Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR ); //registro l'errore e propago l'ecezzione
			throw $e;
		}
		
		return $count;
		
	}
	
	
	/**
	 * Create a random Token number+lecter [0-9][a-z]
	 * @return string token
	 */
	public function randToken(){
		
		$token = '';
		
		for ( $i = 0; $i < $this->lenght; $i++ ){
			$token .= $this->rand();
		}

		return substr($token,0,8);
		
	}
	
	/**
	 * Return a random number or lecter [0-9][a-z]
	 * @return string number or lecter 
	 */
	private function rand(){
		
		$p = rand(1,2);
		
		$n = rand(141,172);

		while ( $n == 148 || $n == 149 || $n == 158 || $n == 159 || $n == 168 || $n == 169 ) {
			$n = rand(141,172);
		}
		
		if ( 1 == $p ) {
			// lettera
			$oct = octdec($n);
			return chr($oct);
		} else {
			// numero
			return rand(0,10); 
		}
		
	}
	
}
	
?>