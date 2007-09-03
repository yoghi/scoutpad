<?php

/**
 * Classe per memorizzare informazioni del Flow (Flow,Token..) in sessione
 * NB: 1 token alla volta!!!
 * @todo estendere la possibilità di avere piu token in sessione!
 */
class Sigma_Flow_Storage_Session implements Sigma_Flow_Storage_Interface {
	
	/**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Sigma_Flow';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'storage';

    /**
     * Object to proxy $_SESSION storage
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $_namespace;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $_member;
    
	
 	/**
     * Sets session storage options and initializes session namespace object
     *
     * @param mixed $namespace
     * @param mixed $member
     */
    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT)
    {
        $this->_namespace = $namespace;
        $this->_member    = $member;
        $this->_session   = new Zend_Session_Namespace($this->_namespace);
    }
	
	/**
     * Returns the session namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->_member;
    }
    
    
    
	/**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If it is impossible to determine whether storage is empty
     * @return boolean
     */
    public function isEmpty(){
    	return !isset($this->_session->{$this->_member});
    }
    
    /**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */
    public function read($find){
    	return $this->_session->{$this->_member};
    }
    
    /**
     *Defined by Sigma_Flow_Storage_Interface
     *
     * @param  mixed $contents
     * @throws Sigma_Flow_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents){
    	$this->_session->lock();
		$this->_session->{$this->_member} = $contents;
		$this->_session->unlock();
    }
	
	/**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear(){
    	unset($this->_session->{$this->_member});
    }
    
    /**
     * Defined by Sigma_Flow_Storage_Interface
     *
     * @throws Sigma_Flow_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clearAll(){
    	
    	foreach($this->session as $member) {
    		unset($this->_session->{$member});
    	}
    }
}

?>