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

ini_set('session.save_path',BASE_DIRECTORY.'/data/tmp');


/**
 * @category	/
 * @package 	Scoutpad
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.3 - 2008 aprile 13 - 14:00 - Stefano Tamagnini
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
			Zend_Loader::loadClass('Zend_Config_Xml');
				
			Zend_Loader::loadClass('Zend_Db');
			Zend_Loader::loadClass('Zend_Db_Table');
			Zend_Loader::loadClass('Zend_Db_Table_Rowset');
				
			Zend_Loader::loadClass('Zend_Log');
				
			Zend_Loader::loadClass('Zend_Controller_Front');
			Zend_Loader::loadClass('Zend_Controller_Router_Rewrite');
			Zend_Loader::loadClass('Zend_Controller_Request_Http');
			Zend_Loader::loadClass('Zend_Controller_Action');
			Zend_Loader::loadClass('Zend_Controller_Dispatcher_Standard');
			Zend_Loader::loadClass('Zend_Controller_Plugin_ErrorHandler');
			Zend_Loader::loadClass('Zend_Config_Exception');	
			
			Zend_Loader::loadClass('Zend_Auth');
			Zend_Loader::loadClass('Zend_Session');
			Zend_Loader::loadClass('Zend_Session_Abstract');
			Zend_Loader::loadClass('Zend_Session_Namespace');
				
			/**
			 * Sigma Class
			 */
			Zend_Loader::loadClass('Sigma_Log');
			Zend_Loader::loadClass('Sigma_Flow_Token');
			Zend_Loader::loadClass('Sigma_Flow_Storage_Interface');
			Zend_Loader::loadClass('Sigma_Controller_Action');
			Zend_Loader::loadClass('Sigma_Controller_Dispatcher');
			Zend_Loader::loadClass('Sigma_Controller_Request_Http');
			Zend_Loader::loadClass('Sigma_Auth_Database_Adapter');
			Zend_Loader::loadClass('Sigma_View_TemplateLite');
				
				
				
		} catch (Zend_Exception $e) {
			var_dump($e->getTrace());
			echo '<h3>'.$e->getMessage().' '.$e->getFile().' riga '.$e->getLine().'</h3>';
		}

		if (!defined('CONFIG_FILE')) {
			define('CONFIG_FILE', BASE_DIRECTORY.'/config/config.ini');
		}
		
		/**
		 * @todo da sistemare ... 
		 */
		if ( !defined('BASE_DIRECTORY') ) {
			define('BASE_DIRECTORY',dirname(dirname(__FILE__)));
		} 

		/*
		 if (!defined('TEMPLATE_LITE_DIR')) {
			define('TEMPLATE_LITE_DIR', BASE_DIRECTORY.'/library/Template_Lite' . DIRECTORY_SEPARATOR);
			require(BASE_DIRECTORY.'/library/Template_Lite/class.template.php');
		}
		*/


	}

	/**
	 * Esegue l'applicazione
	 */
	public function run(){

		try {
				
			try {

				if ( !file_exists(CONFIG_FILE) ) throw new Zend_Config_Exception('Configuration file not exist or not readible ');
				
				$cfg_filename = split("[/\\.]",CONFIG_FILE);
				$n = count($cfg_filename)-1;
				$exts = $cfg_filename[$n];
				
				if ( "xml" != $exts && "ini" != $exts ) throw new Zend_Config_Exception('Configuration file are extension '.$exts.' not allowed ');
				
				if ( "ini" == $exts ) {
					$config = new Zend_Config_Ini(CONFIG_FILE, 'dev');
					Zend_Registry::set('config', $config);
				} 
				
				if ( "xml" == $exts ) {
					$config = new Zend_Config_Xml(CONFIG_FILE, 'dev');
					Zend_Registry::set('config', $config);
				}
					
				set_error_handler(array('scoutpad','error_handler'));
					
				if ( !is_null($config->db->adapter) ) {

					$db = Zend_Db::factory( $config->db->adapter ,$config->db->config->toArray() );
					$db->getConnection(); //controllo se il db esiste davvero!

					Zend_Db_Table::setDefaultAdapter($db);
					Zend_Registry::set('database', $db);

				}
					
				$request = new Sigma_Controller_Request_Http(); //serve veramente?
					
				// setting controller
				$frontController = Zend_Controller_Front::getInstance();
					
				$router = new Zend_Controller_Router_Rewrite();
				
				/**
				 * @see http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.add-config
				 */
				if ( isset($config->routes) ) $router->addConfig($config,'routes');	
					
				$frontController->setRouter($router);
					
				if ( isset( $config->dispatcher->adapter) ) {

					$dispatcher_adapter = $config->dispatcher->adapter;
	
					Zend_Loader::loadClass($dispatcher_adapter);
	
					$dispatcher = new $dispatcher_adapter();
					
					if ( ! $dispatcher instanceof Zend_Controller_Dispatcher_Interface ) throw new Zend_Controller_Dispatcher_Exception('Invalid dispatcher selectet');
					
					$dispatcher->setDefaultModule($config->dispatcher->modules->default);
					
					$frontController->setDispatcher($dispatcher);
				
				}
				
				$frontController->returnResponse(true);
				
				/**
				 * Error Handler MVC
				 */
				$plugErrorHandler = new Zend_Controller_Plugin_ErrorHandler();
				
				if ( isset($config->error->handler) ){
					
					$plugErrorHandler->setErrorHandlerModule($config->error->handler->module);
					$plugErrorHandler->setErrorHandlerController($config->error->handler->controller);
					$plugErrorHandler->setErrorHandlerAction($config->error->handler->action);
					
				}
				
				$frontController->registerPlugin($plugErrorHandler);
					
				// BASE URL
				if (  !is_null($config->base_url) ) $frontController->setBaseUrl($config->base_url);
				
				$log = new Sigma_Log($config->logger);
				Zend_Registry::set('log',$log);
				
				//Zend_Loader::loadClass('Sigma_Flow_Storage_Database');
				//Sigma_Flow_Token::getInstance()->setStorage( new Sigma_Flow_Storage_Database() );
				
				//Zend_Loader::loadClass('Sigma_Flow_Storage_Session');
				//Sigma_Flow_Token::getInstance()->setStorage( new Sigma_Flow_Storage_Session() );
				
				Zend_Registry::set('auth_module', Zend_Auth::getInstance());

			} catch (Zend_Config_Exception $e) {
				echo '<h1>Misconfiguration</h1>';
				echo '<b>Config file : '.CONFIG_FILE.' is invalid</b><br/>';
				echo '<ul><li><b>'.$e->getMessage().'</b></li>';
				echo '</ul>';
				exit;
			} catch (Zend_Log_Exception $e){
				echo '<h1>Misconfiguration</h1>';
				echo '<b>Logger selected in config.ini not avaible or malformatted</b>';
				echo '<ul><li><b>'.$e->getMessage().'</li></ul></b>';
				exit;
			} catch (Zend_Db_Exception $e){
				echo '<h1>Misconfiguration</h1>';
				echo '<b>Database selected in config.ini not avaible</b>';
				echo '<ul><li><b>'.$e->getMessage().'</li></ul></b>';
				exit;
			} catch (Zend_Controller_Router_Exception $e) {
				echo '<h1>Misconfiguration</h1>';
				echo '<b>Invalid routing params in config.ini</b><br/>';
				echo '<ul><li><b>'.$e->getMessage().'</li></ul></b>';
				exit;
			} catch (Zend_Controller_Dispatcher_Exception $e){
				echo '<h1>Misconfiguration</h1>';
				echo '<b>Invalid dispatcher params in config.ini</b><br/>';
				echo '<ul><li><b>'.$e->getMessage().'</li></ul></b>';
				exit;
			} catch (Zend_Exception $e){ //Zend_Controller_Exception 
				echo '<h1>Misconfiguration</h1>';
				echo '<b>Generic problem</b><br/>';
				echo '<ul><li><b>'.$e->getMessage().'</li></ul></b>';
				exit;
			}
			
			//echo xdebug_memory_usage(true).'byte - '.xdebug_time_index().'s <br/>';
			//echo memory_get_usage(true).'<br/>';
			
			//echo xdebug_dump_superglobals();
			
			//echo '<a href="file://'.xdebug_get_profiler_filename().'">Profile</a>';
			
			//var_dump($frontController->getDispatcher()->getControllerDirectory());
				
			$response = $frontController->dispatch($request);
			
			if ($response->isException()) {

				if ( isset($config->error->show) && $config->error->show ){
				
					$e = $response->getException();
	
					$response->outputBody();
					
					echo '<ul>';
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
				
				}
			} else {
				$response->sendResponse();
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

	/**
	 * Error Handler alternativo , non visualizza mai gli errori a schermo
	 */
	public static function error_handler($errno, $errmsg, $filename, $linenum, $vars) {


		$errortype = array (E_ERROR => "Error", E_WARNING => "Warning", E_PARSE => "Parsing Error", E_NOTICE => "Notice", E_CORE_ERROR => "Core Error", E_CORE_WARNING => "Core Warning", E_COMPILE_ERROR => "Compile Error", E_COMPILE_WARNING => "Compile Warning", E_USER_ERROR => "User Error", E_USER_WARNING => "User Warning", E_USER_NOTICE => "User Notice", E_STRICT => "Runtime Notice");

		$dt = date("H:i:s");

		$err = "[ $dt ]\t[ $errortype[$errno] ] $errmsg ";

		if ( $errno != E_USER_NOTICE ) $err .= "\t $filename line $linenum";

		$err .= "\n";

		if ( Zend_Registry::isRegistered('log') ) Zend_Registry::get('log')->log($err, Zend_Log::DEBUG);
		
		//echo "Called @ ". xdebug_call_file(). " : ". xdebug_call_line(). " from ". xdebug_call_function().'<br/>';

	}

}


?>