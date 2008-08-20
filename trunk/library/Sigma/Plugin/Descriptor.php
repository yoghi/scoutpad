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
 * @package    Sigma_Plugin
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma_Plugin
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2008 agosto 20 - 12:07 - Stefano Tamagnini  
 */
class Sigma_Plugin_Descriptor extends Zend_Controller_Plugin_Abstract {
	
	
		/**
		 * Questa classe è un Plugin da eseguire prima di passare il controllo ad un Controller specifico 
		 * carica le impostazioni del modulo che si andra ad utilizzare
		 * @throws Zend_Exception
		 */
		public function __construct(){}
		
		/**
		 * @see 	Zend_Controller_Plugin_Abstract::preDispatch()
		 * @param	Zend_Controller_Request_Abstract $request
		 * @return	void
		 */
		public function preDispatch(Sigma_Controller_Request_Http $request)
		{
			
			/**
			 * Carico il file descriptor.xml all'interno del modulo o leggo il db dei moduli  
			 * e setto i parametri corrispondenti
			 */
			$config = Zend_Registry::get('config');
			
			
			// xml o db ?			
			switch ($config->dispatcher->modules->source) {
    			case 'directory':

    				$directoryModule = $config->dispatcher->modules->directory;
    				
    				if ( !is_dir($directoryModule.$request->getModuleName()) ) {
    					    
	    				$dir = new DirectoryIterator($directoryModule);
				        
				        foreach ($dir as $file) {
				        	
				            if ($file->isDot() || !$file->isDir()) {
				                continue;
				            }				
				
				            $module = $file->getFilename();
				            
				            // Don't use SCCS directories as modules
				            if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
				                continue;
				            }
				
				            $moduleDir = $file->getPathname();
				
				            if ( file_exists($moduleDir. DIRECTORY_SEPARATOR . 'description.xml' ) ) {
				            	
				            	$cfg_locale = new Zend_Config_Xml($moduleDir. DIRECTORY_SEPARATOR . 'description.xml');
				            	
				            	if ( $cfg_locale->name == $request->getModuleName()  ) {
				            		
				            		$path = $moduleDir;
				            		break;
				            		
				            	}
				            	
				            }
				            
				        }

    					
    				} else {
    					
    					$path = $directoryModule.$request->getModuleName();
    					
    				}

    				try {
    				
    					$cfg = new Zend_Config_Xml($path.'/description.xml','options');
    				
    					$this->_configView($config,$cfg);
    					
    				} catch (Zend_Config_Exception $e) {
    					
    					$flow_token = Sigma_Flow_Token::getInstance()->insert('/index/',array('type'=>'errore','text'=> array('Modulo '.$request->getModuleName().' ha description.xml mal formattato!', $e->getMessage()),'next'=>'/index/'));
    					$request->setModuleName('home');
						$request->setControllerName('notify');
						$request->setActionName('index');
    					$request->setParam('id',$flow_token);
    					
    					return;
    					
    				} catch (Zend_Exception $e) {
    					
    					$flow_token = Sigma_Flow_Token::getInstance()->insert('/index/',array('type'=>'errore','text'=> array($e->getMessage()),'next'=>'/index/'));
    					$request->setModuleName('home');
						$request->setControllerName('notify');
						$request->setActionName('index');
    					$request->setParam('id',$flow_token);
    					
    					return;
    					
    				}
    				
    				break;
    				
    			case 'database' :

    				$table_name = $config->dispatcher->modules->tablename;

    				Zend_Loader::loadClass($table_name,BASE_DIRECTORY.'/application/models/tables/');
		
					$modules = new $table_name();
    				
					throw new Exception('NOT IMPLEMENTED YET : Descriptor plugin loading via DB');
					
    				break;
    				
    			default:
    				
    				trigger_error('non uso i descriptor - non conosco la tecnologia utilizzata',E_USER_WARNING);
    				
    				break;
    				
    		}
			
		}
		
		/**
		 * Configurazione del View Engine
		 *
		 * @param Zend_Config $config_globale	configurazione globale
		 * @param Zend_Config $config_locale	configurazione locale
		 */
		private function _configView(Zend_Config $config_globale,Zend_Config $config_locale) {

			$cfg = new Zend_Config($config_globale->toArray(),true);
			$cfg->merge($config_locale);
			
			if ( isset($cfg->view) ){
				
				/**
				 * @var Zend_Controller_Action_Helper_ViewRenderer
				 */
				$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
				
				/**
				 * View Renderer
				 * Template Lite o Zend_View normale o altro...
				 */
				if ( isset($cfg->view->adapter) ) {
					
					$view_engine_name = $cfg->view->adapter;
					
					Zend_Loader::loadClass($view_engine_name);
					$view_engine = new $view_engine_name();
					
					//il ViewRenderer aggiunge cmq. la path di default anche se questa non è settata
					if ( !is_null($cfg->view->basePath) ){
						$view_engine->setBasePath($cfg->view->basePath);
					}
					
					if ( !is_null($cfg->view->suffix) ){
						$viewRenderer->setViewSuffix($cfg->view->suffix);
					}
					
					$viewRenderer->setView($view_engine);
					
				} else {
					//disabilito il rendering automatico!!
					$viewRenderer->setNoRender(true);
				}
				
				if ( isset($cfg->layout->layoutPath) ) {
					
					Zend_Loader::loadClass('Zend_Layout');
					$layout = new Zend_Layout(null,true);
					
					$layout->setLayoutPath($cfg->layout->layoutPath);

					if ( isset($cfg->layout->template) ){
						$layout->setLayout($cfg->layout->template);
					} else {
						$layout->setLayout('default');
					}
					
					if ( isset($cfg->layout->contentKey) ){
						$layout->setContentKey($cfg->layout->contentKey);
					} else {
						$layout->setContentKey('content');
					}
					
					//$layout->setView($view_engine);
					
					//Zend_View_Helper_Layout
					//.. bug => layout() dentro il template cerca l'helper layout e ha problemi a caricarlo!!
					
					/**
					 * Layout comune 
					 */
					/*$inflector = new Zend_Filter_Inflector(':script.:suffix');
					$inflector->addRules(array(':script' => array('Word_CamelCaseToDash', 'StringToLower'),
								 								  'suffix'  => $config->layout->suffix));
					 
					// Initialise Zend_Layout's MVC helpers
					Zend_Layout::startMvc(array('layoutPath' => ROOT_DIR.$config->layout->layoutPath,
												'view' => $view,
												'contentKey' => $config->layout->contentKey,
												'inflector' => $inflector));
					*/
					
				}
				
				
			}
			
		}
	
}