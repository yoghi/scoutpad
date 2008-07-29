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
 * @package    Sigma_Controller
 * @copyright  Copyright (c) 2007 Stefano Tamagnini
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */


/**
 * @category	/
 * @package 	Sigma_Controller
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.3 - 2008 luglio 29 - 15:00 - Stefano Tamagnini
 */
class Sigma_Controller_Dispatcher extends Zend_Controller_Dispatcher_Standard {
	
	private $_directoryModule = '';
	
	/**
     * Constructor: Set current module to default value
     *
     * @param array|Zend_Config $options opzioni (Es. caricare da database)
     * @param array $params
     * @return void
     */
    public function __construct(array $params = array())
    {
    	
        parent::__construct($params);
        $this->_curModule = $this->getDefaultModule();
        $this->_directoryModule = BASE_DIRECTORY.'/application/modules/';
        
        $cfg = Zend_Registry::get('config');
        if ( isset ($cfg->dispatcher) ) $this->setOptions($cfg->dispatcher->toArray());
        
    }
    
    public function setOptions(array $options){ 
    	
    	if (isset($options['modules'])) {
    		
    		if ( isset($options['modules']['source']) ) {

    			switch ($options['modules']['source']) {
    				case 'directory':
    					
    					if ( isset( $options['modules']['directory'] ) ) $this->_directoryModule = $options['modules']['directory'];
    					$this->_loadFromDirectory();
    					
    					break;
    				case 'database' :
    					
    					if ( !Zend_Registry::isRegistered('database') ) throw new Zend_Controller_Dispatcher_Exception('Sigma_Controller_Dispatcher with db required avaible database');
    					if ( !isset($options['modules']['tablename']) ) throw new Zend_Log_Exception('Sigma_Controller_Dispatcher with db required tablename params in config.ini');
    					
    					//$db = Zend_Registry::get('database');
    					
    					$this->_loadFromDb();
    					
    					break;
    				default:
    					break;
    			}

    		}
    		
    	}
    	
    	return $this;
    }
    
    public function setConfig(Zend_Config $config){
    	return $this->setOptions($config->toArray()); 
    }
	
	private function _loadFromDb(){ 
		
		Zend_Loader::loadClass('Modules',BASE_DIRECTORY.'/application/models/tables/');
		
		$modules = new Modules();
		$r = $modules->fetchAllActive();
		
		foreach($r as $mod) {
			$this->addControllerDirectory(BASE_DIRECTORY.'/application/modules/'.$mod->path_name,$mod->name);
		}
		
	}
	
	/**
	 * Carico i moduli da una directory
	 * NB: tutti i moduli presenti saranno considerati ATTIVI e VALIDI
	 * 
	 * @param Zend_Db $db in caso sia possibile salvare i dati ricavati su un database.
	 */
	private function _loadFromDirectory(){		
		
		//scandisco la directory e per ogni cartella cerco il file description.xml o description.ini

		try{
            $dir = new DirectoryIterator($this->_directoryModule);
        }catch(Exception $e){
            throw new Zend_Controller_Exception("Default modules directory not readable ");
        }
        
        foreach ($dir as $file) {
        	
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            $module    = $file->getFilename();

            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
                continue;
            }

            $moduleDir = $file->getPathname();

            if ( file_exists($moduleDir. DIRECTORY_SEPARATOR . 'description.xml' ) ) {
            	
            	$cfg = new Zend_Config_Xml($moduleDir. DIRECTORY_SEPARATOR . 'description.xml');
            	
            	$this->addControllerDirectory($moduleDir . DIRECTORY_SEPARATOR . 'controllers' , $cfg->name );
				
            }
            
        }
		
	}
	
	
	
}


?>