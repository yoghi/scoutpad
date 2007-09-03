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
     * Lenght of token used into forms 
     *
     * @var int
     */
    private $lenght = 8;
	
    /**
     * Where storage token
     *
     * @var Sigma_Flow_Storage_Interface
     */
    private $storage = null;
    
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
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
	private function __construct(){}
	
	/**
	 * Inserisce un token in memoria
	 *
	 * @param string $reference l'url di riferimento sorgente (da dove provengo)
	 * @param array $info l'informazione da memorizzare
	 * 
	 * @return string token code 
	 * @throws Sigma_Flow_Storage_Exception
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
			
			$this->storage->write($data);
	
		} catch(Sigma_Flow_Storage_Exception $e){
			
			try {
				$data['token'] = $this->randToken();
				Zend_Registry::get('log')->log('Provo un diverso token',Zend_Log::WARN );
				$this->storage->write($data);
			} catch (Sigma_Flow_Storage_Exception $e){
				throw $e;
			}
			
		}
		
		return $data['token'];
	}
	
	/**
	 * Restituisce il contenuto del Token
	 * @param string $token il token da cercare
	 * @return mixed
	 */
	public function getTokenContent($token_id){
		
		try {
			
			return $this->storage->read($token_id);
	
		} catch(Sigma_Flow_Storage_Exception $e){
			
				return null;
			
		}
		
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
	
	public function setStorage( Sigma_Flow_Storage_Interface $s ){
		$this->storage = $s;
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