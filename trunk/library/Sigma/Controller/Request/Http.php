<?php


/**
 * Estensione della versione base 
 * 
 * @category	Sigma_Controller_Request
 * @package 	Scoutpad
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 settembre 16 - 14:00 - Stefano Tamagnini
 */
class Sigma_Controller_Request_Http extends Zend_Controller_Request_Http {
	
	/**
	 * Ultima pagina visitata
	 *
	 * @var string 
	 */
	protected $lastpage = null;
	
	/**
     * Constructor
     *
     * If a $uri is passed, the object will attempt to populate itself using
     * that information.
	 *
	 * @param string|Zend_Uri $uri 
	 */
	public function __construct($uri = null){
		parent::__construct($uri);
		//$this->lastpage = $this->getModuleName().'/'.$this->getControllerName();
	}
	
	/**
	 * Restituisce la pagina di provenienza se esiste
	 * 
	 */
	public function getFromPage(){
		//return $this->lastpage;
		throw new Exception('getFromPage() - Not Implemented yet');
	}
	
}

?>