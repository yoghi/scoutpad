<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/home/workspace/Scout/ScoutPad/library');

date_default_timezone_set('Europe/Rome');
ini_set('session.save_path','/tmp');

include 'Zend.php';
include 'Zend/Loader.php';

Zend_Loader::loadClass('Zend_Registry');
Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_View');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Controller_Action');
Zend_Loader::loadClass('Zend_Controller_Router_Rewrite');
Zend_Loader::loadClass('Zend_Controller_Dispatcher_Standard');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Filter');
Zend_Loader::loadClass('Sigma_Auth_Database_Adapter');
Zend_Loader::loadClass('Sigma_View_TemplateLite');
Zend_Loader::loadClass('Zend_Log');


if (!defined('TEMPLATE_LITE_DIR')) {
	define('TEMPLATE_LITE_DIR', '/home/workspace/Scout/ScoutPad/library/Sigma/Template_Lite' . DIRECTORY_SEPARATOR);
	require('/home/workspace/Scout/ScoutPad/library/Sigma/Template_Lite/class.template.php');
}

function sigma_error_handler($errno, $errmsg, $filename, $linenum, $vars) {

	
	$errortype = array (E_ERROR => "Error", E_WARNING => "Warning", E_PARSE => "Parsing Error", E_NOTICE => "Notice", E_CORE_ERROR => "Core Error", E_CORE_WARNING => "Core Warning", E_COMPILE_ERROR => "Compile Error", E_COMPILE_WARNING => "Compile Warning", E_USER_ERROR => "User Error", E_USER_WARNING => "User Warning", E_USER_NOTICE => "User Notice", E_STRICT => "Runtime Notice");

	$dt = date("H:i:s");

	$err = "[ $dt ]\t[ $errortype[$errno] ] $errmsg ";
	
	if ( $errno != E_USER_NOTICE ) $err .= "\t $filename line $linenum";
	
	$err .= "\n";
	
	Zend_Log::log($err, Zend_Log::LEVEL_DEBUG);
	
}

set_error_handler("sigma_error_handler");

try {

	// load configuration
	try {
		$config = new Zend_Config_Ini('/home/workspace/Scout/ScoutPad/application/config.ini', 'general');
		Zend_Registry::set('config', $config);
	} catch (Zend_Config_Exception $e) {
		echo '<h1>Misconfiguration</h1>';
		echo '<b>config.ini not found or not readable</b>';
		exit;
	}
	
	if ( !is_null($config->db->adapter) ) {
		//database 
		try {
			$db = Zend_Db::factory( $config->db->adapter ,$config->db->config->asArray() );
			Zend_Registry::set('database', $db);
			Zend_Db_Table::setDefaultAdapter($db);
		} catch (Zend_Db_Exception $e){
			echo '<h1>Misconfiguration</h1>';
			echo '<b>Database selected in config.ini not avaible</b>';
			exit;
		}
		//Loggo su tabella
		Zend_Loader::loadClass('Zend_Log_Adapter_Db');
		Zend_Log::registerLogger(new Zend_Log_Adapter_Db($db,'logging'));
		
	} else {
		
		//Loggo su file
		Zend_Loader::loadClass('Zend_Log_Adapter_File');
		Zend_Log::registerLogger(new Zend_Log_Adapter_File('/tmp/debug.txt'));
		
	}
	//Zend_Log::registerLogger(new Zend_Log_Adapter_Console(), 'Console');
	//esempio
	//Zend_Log::log('A serious error has occurred.', Zend_Log::LEVEL_SEVERE);

	// register the input filters
	/**
	 * @todo: create generic chain for filtering input
	 */  
	Zend_Registry::set('filter',new Zend_Filter());
	
	// Autentication
	Zend_Registry::set('auth_module', Zend_Auth::getInstance());
	
	// Session
	//require_once 'Zend/Session.php';
	//Zend_Session_Core::setOptions(array('remember_me_seconds' => $config->session->live, 'name' => $config->session->name));
	//Zend::register('session_module', new Zend_Session($config->session->name));
	
	// setting controller
	$frontController = Zend_Controller_Front::getInstance();
	$frontController->setControllerDirectory(array( 
      'default' => '/home/workspace/Scout/ScoutPad/application/default/controllers',
      'rubrica' => '/home/workspace/Scout/ScoutPad/application/rubrica/controllers',
      'admin' => '/home/workspace/Scout/ScoutPad/application/admin/controllers'
	)); 
	$frontController->setRouter(new Zend_Controller_Router_Rewrite());
	$frontController->setDispatcher(new Zend_Controller_Dispatcher_Standard());
	
	//run
	$request = new Zend_Controller_Request_Http();
	
	Zend_Loader::loadClass('Sigma_Plugin_Auth');
	$frontController->registerPlugin(new Sigma_Plugin_Auth());
	//$frontController->throwExceptions(true); //attivo l'uso delle ecezzioni al difuori di Zend!!!
    //$controller->setParam('sitemap', $sitemap);
    $frontController->returnResponse(true);
	$response = $frontController->dispatch($request);
	
	if ($response->isException()) {
	    $e = $response->getException();
	    // handle exceptions ...
	    echo '<h1>Errore:</h1><ul>';
	    foreach($e as $exceptions){
	    	echo '<li>';
	    	echo "<b>" . get_class($exceptions) . '</b><br/>';
	    	echo $exceptions->getMessage().'<br/>';
	    	echo $exceptions->getFile().', '.$exceptions->getLine().'<br/>';
	    	echo '</li>';
	    }
	    echo '</ul>';
	} else {
	    $response->sendHeaders();
	    $response->outputBody();
	}

	
}
catch (Exception $e){
	echo '<h1>Errore:</h1> '.$e->getMessage();
}
?>