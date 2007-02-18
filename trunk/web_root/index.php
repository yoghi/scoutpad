<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/home/workspace/Scout/ScoutPad/library');

date_default_timezone_set('Europe/Rome');

include 'Zend.php';

Zend::loadClass('Zend_Controller_Front');
Zend::loadClass('Zend_Controller_RewriteRouter');
Zend::loadClass('Zend_View');
Zend::loadClass('Zend_Config_Ini');
Zend::loadClass('Zend_Db');
Zend::loadClass('Zend_Db_Table');
Zend::loadClass('Zend_Filter_Input');
Zend::loadClass('Zend_Controller_Action');
Zend::loadClass('Zend_Controller_ModuleRouter');
Zend::loadClass('Zend_Controller_ModuleDispatcher');
Zend::loadClass('Zend_Auth');
Zend::loadClass('Sigma_Auth_Database_Adapter');
Zend::loadClass('Sigma_View_TemplateLite');

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

	// DEBUG
	require_once 'Zend/Log.php';                // Zend_Log base class
	require_once 'Zend/Log/Adapter/File.php';   // File log adapter
	// Register the file logger
	Zend_Log::registerLogger(new Zend_Log_Adapter_File('/tmp/debug.txt'));
	// Register the console logger
	//require_once 'Zend/Log/Adapter/Console.php'; // Console log adapter
	//Zend_Log::registerLogger(new Zend_Log_Adapter_Console(), 'Console');
	//esempio
	//Zend_Log::log('A serious error has occurred.', Zend_Log::LEVEL_SEVERE);
	
	Zend_Log::log('---------------------------------------------------------------------------', Zend_Log::LEVEL_INFO);

	// register the input filters
	Zend::register('post', new Zend_Filter_Input($_POST));
	Zend::register('get', new Zend_Filter_Input($_GET));

	// load configuration
	$config = new Zend_Config_Ini('/home/workspace/Scout/ScoutPad/application/config.ini', 'general');

	// setup database
	Zend::register('config', $config);
	$db = Zend_Db::factory( $config->db->adapter ,$config->db->config->asArray() );
	Zend::register('database', $db);
	Zend_Db_Table::setDefaultAdapter($db);

	// Autentication
	Zend::register('auth_module', Zend_Auth::getInstance());
	
	// Session
	//require_once 'Zend/Session.php';
	//Zend_Session_Core::setOptions(array('remember_me_seconds' => $config->session->live, 'name' => $config->session->name));
	//Zend::register('session_module', new Zend_Session($config->session->name));
	

	//Setup controller
//	$router = new Zend_Controller_RewriteRouter();
//	$frontController->setRouter($router);
//	$frontController->setControllerDirectory('/home/workspace/Scout/ScoutPad/application/controllers');
	//$baseUrl = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/index.php'));
	//$router->setRewriteBase($baseUrl);
	
	// setting controller
	$frontController = Zend_Controller_Front::getInstance();
	$frontController->setControllerDirectory(array( 
      'default' => array('/home/workspace/Scout/ScoutPad/application/default/controllers'),
      'rubrica' => '/home/workspace/Scout/ScoutPad/application/rubrica/controllers',
      'admin' => '/home/workspace/Scout/ScoutPad/application/admin/controllers'
	)); 
	$frontController->setRouter(new Zend_Controller_ModuleRouter()); 
	$frontController->setDispatcher(new Zend_Controller_ModuleDispatcher());
	
	//run
	$request = new Zend_Controller_Request_Http();
	
	Zend::loadClass('Sigma_Plugin_Auth');
	$frontController->registerPlugin(new Sigma_Plugin_Auth());
	$frontController->throwExceptions(true); //attivo l'uso delle ecezzioni al difuori di Zend!!!
    //$controller->setParam('sitemap', $sitemap);
	$response = $frontController->dispatch($request);

	//
	//if($response->isException())
	//{
	//    echo $response->getException();
	//    exit; // Stop here - ;)
	//}
	//
	///*
	// * Echo the response (with headers) to client
	// * Zend_Controller_Response_Http implements
	// * __toString().
	// */
	//echo $response;
	
}
catch (Exception $e){
	echo '<h1>Errore:</h1> '.$e->getMessage();
}
?>