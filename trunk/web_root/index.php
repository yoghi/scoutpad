<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '/home/workspace/Scout/ScoutPad/library');

date_default_timezone_set('Europe/Rome');
ini_set('session.save_path','/tmp');

include 'Zend/Loader.php';


/**
 * Zend Class
 */
Zend_Loader::loadClass('Zend_Registry');
Zend_Loader::loadClass('Zend_View');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Db_Table_Rowset');
Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_Controller_Action');
Zend_Loader::loadClass('Zend_Controller_Router_Rewrite');
Zend_Loader::loadClass('Zend_Controller_Dispatcher_Standard');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Session');
Zend_Loader::loadClass('Zend_Session_Abstract');
Zend_Loader::loadClass('Zend_Session_Namespace');
Zend_Loader::loadClass('Zend_Log');
Zend_Loader::loadClass('Zend_Log_Writer_Stream');
Zend_Loader::loadClass('Zend_Log_Formatter_Xml');

/**
 * Sigma Class
 */
Zend_Loader::loadClass('Sigma_Controller_Action');
Zend_Loader::loadClass('Sigma_Auth_Database_Adapter');
Zend_Loader::loadClass('Sigma_View_TemplateLite');

Zend_Loader::loadClass('Sigma_Form_Help');



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
	
	Zend_Registry::get('log')->log($err, Zend_Log::DEBUG);
	
}

set_error_handler("sigma_error_handler");

try {
	
	$log = new Zend_Log();
	Zend_Registry::set('log',$log);
	//formatter default `timestamp`, `message`, `priority`, `priorityName`
	$formatter = new Zend_Log_Formatter_Xml();
	$stream = new Zend_Log_Writer_Stream('/tmp/debug.txt');
	$stream->setFormatter($formatter);
	$log->addWriter($stream); //sicuramente su file

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
		Zend_Loader::loadClass('Zend_Log_Writer_Db');
		$log->addWriter(new Zend_Log_Writer_Db($db,'Log')); //ora anche su db
	}
	
	// Autentication
	Zend_Registry::set('auth_module', Zend_Auth::getInstance());

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
	
	echo Sigma_Form_Help::getInstance()->randToken();
	
	/*for ( $i = 141; $i < 185; $i++ ){
		$oct = octdec($i);
		echo "$i => ".chr($oct)."\n";
	}
	*/
	
}
catch (Exception $e){
	echo '<h1>Errore:</h1> '.$e->getMessage();
	echo '<pre>';
	print_r($e->getTrace());
	echo '</pre>';
}
?>