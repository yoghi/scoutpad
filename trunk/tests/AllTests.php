<?php

define('BASE_DIRECTORY',dirname(dirname(__FILE__))); 

require_once BASE_DIRECTORY.'/library/Zend/Loader.php';

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

/**
 * Tutti i test di base
 */
class AllTests
{
	public static function main()
	{

		PHPUnit_TextUI_TestRunner::run(self::suite(), $parameters);
	}

	public static function suite()
	{
		try {
			$suite = new PHPUnit_Framework_TestSuite('Scoutpad');

			require_once 'BootstrapTest.php';
			$suite->addTestSuite('BootstrapTest');				// classe diretta
			
			require_once 'library/Sigma/AllTest.php';
			$suite->addTest(Library_Sigma_AllTests::suite()); 	// una directory
		
			return $suite;
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}

?>