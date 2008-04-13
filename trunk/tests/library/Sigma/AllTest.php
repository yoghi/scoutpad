<?php

class Library_Sigma_AllTests extends PHPUnit_Framework_TestCase
{
    
	public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite()
    {
    	
        $suite = new PHPUnit_Framework_TestSuite('Scoutpad - Sigma - Library ');
        
        require_once 'library/Sigma/ProvaTest.php';
        require_once 'library/Sigma/AclTest.php';
        $suite->addTestSuite('Library_Sigma_ProvaTest');
        $suite->addTestSuite('Library_Sigma_AclTest');
        
        return $suite;
    }
    
}

?>