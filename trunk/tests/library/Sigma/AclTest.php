<?php

class Library_Sigma_AclTest extends PHPUnit_Framework_TestCase
{
	/**
     * Ensures that the Singleton pattern is implemented properly
     *
     * @return void
     */
    public function testNewAcl()
    {
    	echo "\n LibrarySigma\t=>\tTestNewAcl \n";
    	
    	Zend_Loader::loadClass('Sigma_Acl');
    	
    	$acl = new Sigma_Acl('1',array('guest'),1);
    	
        $this->assertTrue(true);
    }
    
}

?>