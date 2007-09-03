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
 * @version		0.0.1 - 2007 maggio 28 - 22:00 - Stefano Tamagnini  
 */
interface Sigma_Flow_Storage_Interface {
	
	
	/**
     * Returns true if and only if storage is empty
     *
     * @throws Sigma_Flow_Storage_Exception If it is impossible to determine whether storage is empty
     * @return boolean
     */
    public function isEmpty();
    
    /**
     * Returns the contents of storage (on multiple possibility, use a specific id, Es. Memeber in Storage_Session
     *
     * Behavior is undefined when storage is empty.
     *
     * @param mixed $idtoken
     * @throws Sigma_Flow_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */
    public function read($idtoken);
    
    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws Sigma_Flow_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents);
	
	/**
     * Clears contents from storage
     *
     * @throws Sigma_Flow_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear();
    
    /**
     * Clears all contents from storage
     *
     * @throws Sigma_Flow_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clearAll();
}

?>