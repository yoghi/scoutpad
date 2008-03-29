<?php

/**
 * Scoutpad
 *
 * LICENSE
 *
 * This source file is subject to the New-BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   /
 * @package    Scoutpad
 * @copyright  Copyright (c) 2007 Stefano Tamagnini
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */

set_include_path(get_include_path() . PATH_SEPARATOR . BASE_DIRECTORY .'/library');

require_once 'Zend/Loader.php';

date_default_timezone_set('Europe/Rome');

ini_set('session.save_path','/tmp');


/**
 * @category	/
 * @package 	Scoutpad
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 maggio 13 - 19:00 - Stefano Tamagnini
 */
class Scoutpad {

	/**
	 * Singleton instance
	 *
	 * @var Scoutpad
	 */
	protected static $_instance = null;

	/**
	 * Returns an instance of Scoutpad, Singleton pattern implementation
	 *
	 * @return Scoutpad
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Costruttore privato (singleton)
	 */
	private function __construct(){

		try {
			
			/**
			 * Zend Class
			 */
			Zend_Loader::loadClass('Zend_Registry');
			Zend_Loader::loadClass('Zend_View');
			Zend_Loader::loadClass('Zend_Config_Ini');
			
			Zend_Loader::loadClass('Zend_Db');
			Zend_Loader::loadClass('Zend_Db_Table');
			Zend_Loader::loadClass('Zend_Db_Table_Rowset');
			
			Zend_Loader::loadClass('Zend_Log');
			Zend_Loader::loadClass('Zend_Log_Writer_Stream');
			Zend_Loader::loadClass('Zend_Log_Formatter_Xml');
			
			Zend_Loader::loadClass('Zend_Controller_Front');
			Zend_Loader::loadClass('Zend_Controller_Router_Rewrite');
			Zend_Loader::loadClass('Zend_Controller_Request_Http');
			Zend_Loader::loadClass('Zend_Controller_Action');
			Zend_Loader::loadClass('Zend_Controller_Dispatcher_Standard');
			
			Zend_Loader::loadClass('Zend_Auth');
			Zend_Loader::loadClass('Zend_Session');
			Zend_Loader::loadClass('Zend_Session_Abstract');
			Zend_Loader::loadClass('Zend_Session_Namespace');
			
			/**
			 * Sigma Class
			 */
			Zend_Loader::loadClass('Sigma_Flow_Token');
			Zend_Loader::loadClass('Sigma_Flow_Storage_Interface');
			Zend_Loader::loadClass('Sigma_Controller_Action');
			Zend_Loader::loadClass('Sigma_Controller_Request_Http');
			Zend_Loader::loadClass('Sigma_Auth_Database_Adapter');
			Zend_Loader::loadClass('Sigma_View_TemplateLite');
			
			
			
		} catch (Zend_Exception $e) {
			var_dump($e->getTrace());
			echo '<h3>'.$e->getMessage().' '.$e->getFile().' riga '.$e->getLine().'</h3>';
		}

		if (!defined('CONFIG_FILE')) {
			define('CONFIG_FILE', BASE_DIRECTORY.'/application/config.ini');
		}

		if (!defined('TEMPLATE_LITE_DIR')) {
			define('TEMPLATE_LITE_DIR', BASE_DIRECTORY.'/library/Template_Lite' . DIRECTORY_SEPARATOR);
			require(BASE_DIRECTORY.'/library/Template_Lite/class.template.php');
		}

		set_error_handler(array('scoutpad','error_handler'));


	}

	/**
	 * Esegue l'applicazione
	 */
	public function run(){
		
		try {

			$log = new Zend_Log();
			Zend_Registry::set('log',$log);
			//formatter default `timestamp`, `message`, `priority`, `priorityName`

			$formatter = new Zend_Log_Formatter_Xml();
			$stream = new Zend_Log_Writer_Stream('/tmp/debug.xml');
			$stream->setFormatter($formatter);
			$log->addWriter($stream); //sicuramente su file

			$stream_debug = new Zend_Log_Writer_Stream('/tmp/debug.txt');
			$log->addWriter($stream_debug);

			// load configuration
			try {
				$config = new Zend_Config_Ini(CONFIG_FILE, 'general');
				Zend_Registry::set('config', $config);
			} catch (Zend_Config_Exception $e) {
				echo '<h1>Misconfiguration</h1>';
				echo '<b>config.ini not found or not readable</b>';
				exit;
			}

			if ( !is_null($config->db->adapter) ) {
				//database
				try {

					$db = Zend_Db::factory( $config->db->adapter ,$config->db->config->toArray() );
					$db->getConnection(); //controllo se il db esiste davvero!

					Zend_Db_Table::setDefaultAdapter($db);
					Zend_Registry::set('database', $db);

				} catch (Zend_Db_Exception $e){
					echo '<h1>Misconfiguration</h1>';
					echo '<b>Database selected in config.ini not avaible</b>';
					exit;
				}
				//Loggo su tabella
				Zend_Loader::loadClass('Zend_Log_Writer_Db');
				$log->addWriter(new Zend_Log_Writer_Db($db,'Log')); //ora anche su db, ATTENTO non controlla in automatico la connettività


				// Flow Control
				Zend_Loader::loadClass('Sigma_Flow_Storage_Database');
				Sigma_Flow_Token::getInstance()->setStorage( new Sigma_Flow_Storage_Database() );

			} else {

				// no database

				// Flow Control
				Zend_Loader::loadClass('Sigma_Flow_Storage_Session');
				Sigma_Flow_Token::getInstance()->setStorage( new Sigma_Flow_Storage_Session() );

			}

			// Autentication
			Zend_Registry::set('auth_module', Zend_Auth::getInstance());

			/**
			 * require_once 'Zend/Controller/Request/Apache404.php'; 
			 *	$request = new Zend_Controller_Request_Apache404(); 
			 */

			// setting controller
			$frontController = Zend_Controller_Front::getInstance();
			$frontController->setRouter(new Zend_Controller_Router_Rewrite());
			
			/**
			 * Se volessi cambiare il dispatcher di default lo dovrei fare prima di settare le directory oppure settando le directory direttamente nell'oggetto dispatcher
			 */
			$frontController->setControllerDirectory(array(
				'default' => '/home/workspace/Scout/ScoutPad/application/default/controllers',
				'rubrica' => '/home/workspace/Scout/ScoutPad/application/rubrica/controllers',
				'admin' => '/home/workspace/Scout/ScoutPad/application/admin/controllers'
			));
			
			
			//$frontController->setBaseUrl('/application/myapp');

			/**
			 * errore "script 'script/login.phtml' not found in path dovuto ai nuovi helper introdotti"
			 * @see http://www.nabble.com/got-Zend_View_Exception-t3814949s16154.html
			 * @todo sarebbe meglio riscrivere l'helper
			 */
			$frontController->setParam('noViewRenderer',true);


			/*Zend_Loader::loadClass('Sigma_Flow_Storage_Interface');
			 Zend_Loader::loadClass('Sigma_Flow_Storage_Session');
			 $r = new Sigma_Flow_Storage_Session();
			 $r->write(array(2,3,5,'p'));
			 $r->clear();
			 var_dump($r->isEmpty());*/

			//run
			$request = new Sigma_Controller_Request_Http();

			Zend_Loader::loadClass('Sigma_Plugin_Auth');
			$frontController->registerPlugin(new Sigma_Plugin_Auth());
			
			//attivo l'uso delle ecezzioni al difuori di Zend!!! Utile in caso di dev...
			//$frontController->throwExceptions(true); 
			//$controller->setParam('sitemap', $sitemap);
			
			$frontController->returnResponse(true);
			$response = $frontController->dispatch($request);

			if ($response->isException()) {
				$e = $response->getException();
				// handle exceptions ...
				echo '<div style="font:12px/1.35em arial, helvetica, sans-serif;"><div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">';
				echo '<h1>Errore:</h1><ul>';
				echo 'Siamo spiacenti ma un errore interno ha portato all\'impossibilit&agrave; di completare la richiesta.';
				echo '</div></div>';
				foreach($e as $exceptions){
					echo '<li>';
					echo "<b>" . get_class($exceptions) . '</b><br/>';
					echo $exceptions->getMessage().'<br/>';
					echo $exceptions->getFile().', '.$exceptions->getLine().'<br/>';
					echo '</li>';
					echo '<pre>';
					echo $exceptions->getTraceAsString();
					echo '</pre>';
				}
				echo '</ul>';
			} else {
				$response->sendHeaders();
				$response->outputBody();
				//echo '<a href="/',$request->getFromPage(),'">',$request->getFromPage(),'</a>';
			}

		}
		catch (Exception $e){
			echo '<h1>Errore:</h1> '.$e->getMessage();
			echo '<pre>';
			print_r($e->getTrace());
			echo '</pre>';
		}
	}


	public static function error_handler($errno, $errmsg, $filename, $linenum, $vars) {


		$errortype = array (E_ERROR => "Error", E_WARNING => "Warning", E_PARSE => "Parsing Error", E_NOTICE => "Notice", E_CORE_ERROR => "Core Error", E_CORE_WARNING => "Core Warning", E_COMPILE_ERROR => "Compile Error", E_COMPILE_WARNING => "Compile Warning", E_USER_ERROR => "User Error", E_USER_WARNING => "User Warning", E_USER_NOTICE => "User Notice", E_STRICT => "Runtime Notice");

		$dt = date("H:i:s");

		$err = "[ $dt ]\t[ $errortype[$errno] ] $errmsg ";

		if ( $errno != E_USER_NOTICE ) $err .= "\t $filename line $linenum";

		$err .= "\n";

		Zend_Registry::get('log')->log($err, Zend_Log::DEBUG);

	}

}


?>