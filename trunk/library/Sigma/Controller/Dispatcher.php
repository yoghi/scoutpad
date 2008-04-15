<?php



class Sigma_Controller_Dispatcher extends Zend_Controller_Dispatcher_Standard {
	
	private $_directoryModule = '';
	
	/**
     * Constructor: Set current module to default value
     *
     * @param array|Zend_Config $options opzioni (Es. caricare da database)
     * @param array $params
     * @return void
     */
    public function __construct($options = null,array $params = array())
    {
        parent::__construct($params);
        $this->_curModule = $this->getDefaultModule();
        $this->_directoryModule = BASE_DIRECTORY.'/application/modules/';
        
    	if (is_array($config)) {
			$this->setOptions($config);
        } elseif ($config instanceof Zend_Config) {
			$this->setConfig($config);
        } else {
        	$this->_loadFromDirectory();
        }
        
    }
    
    public function setOptions(array $options){
    	
    	if (isset($options['modules'])) {
    		
    		if ( isset($options['modules']['source']) ) {

    			switch ($options['modules']['source']) {
    				case 'directory':
    					
    					if ( isset ( $options['modules']['directory'] ) ) $this->_directoryModule = $this->$options['modules']['directory'];
    					else $this->_directoryModule = $this->_directoryModule;
    					
    					$this->_loadFromDirectory();
    					
    					break;
    				case 'database' :
    					
    					if ( !Zend_Registry::isRegistered('database') ) throw new Zend_Controller_Dispatcher_Exception('Sigma_Controller_Dispatcher with db required avaible database');
    					if ( !isset($options['modules']['tablename']) ) throw new Zend_Log_Exception('Sigma_Controller_Dispatcher with db required tablename params in config.ini');
    					
    					$db = Zend_Registry::get('database');
    					
    					$this->_loadFromDb($db);
    					
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
	
	private function _loadFromDb(Zend_Db $db){
		Zend_Loader::loadClass('Modules',BASE_DIRECTORY.'/application/models/tables/');
		...
	}
	
	/**
	 * Carico i moduli da una directory
	 * NB: tutti i moduli presenti saranno considerati ATTIVI e VALIDI
	 */
	private function _loadFromDirectory(){
		$front->addModuleDirectory($this->_directoryModule);
	}
	
	
	
}


?>