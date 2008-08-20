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
 * @package    Sigma
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * @category	Sigma
 * @package 	Sigma
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.3 - 2008 aprile 13 - 17:00 - Stefano Tamagnini 
 */
class Sigma_Log extends Zend_Log {
	
	const AUDIT   = 8;  // Audit: audit messages
	
	/**
	 * Costruttore di base
	 * 
	 * @param array|Zend_Config $config configurazione
	 * @param Zend_Log_Writer_Abstract $writer dove scrivere i log [Null is valid] 
	 */
	public function __construct($config = null,Zend_Log_Writer_Abstract $writer = null){
		
		parent::__construct($writer);
		
		Zend_Loader::loadClass('Zend_Log_Exception');
		
		$this->setEventItem('visitorIp', $_SERVER['REMOTE_ADDR']);
  		$this->setEventItem('requestMethod', $_SERVER['REQUEST_METHOD'] );
  		$this->setEventItem('requestUrl', $_SERVER['REQUEST_URI'] );
		
		if (is_array($config)) {
            $this->setOptions($config);
        } elseif ($config instanceof Zend_Config) {
            $this->setConfig($config);
        }
        
        //$logger->addPriority('AUDIT', 8);
        
//        Zend_Loader::loadClass('Zend_Log_Writer_Firebug');
//        $writerF = new Zend_Log_Writer_Firebug();
//        $this->addWriter($writerF);
        
	}
	
	/**
     * Set log state from options array
     * 
     * @param  array $config 
     * @return Sigma_Log
     */
	public function setOptions(array $options){
		
		if (isset($options['appender'])) {
            
			foreach ($options['appender'] as $appender_name => $appender){
				
				if ( !isset($appender['type']) ) throw new Zend_Log_Exception('Sigma_log required appender type params in config.ini');
				if ( !isset($appender['level']) ) throw new Zend_Log_Exception('Sigma_log required appender level params in config.ini');
				
				switch ($appender['type']) {
					case 'Zend_Log_Writer_Stream':
						
						Zend_Loader::loadClass('Zend_Log_Writer_Stream');
						$l = new $appender['type'](BASE_DIRECTORY.'/data/logs/'.$appender['stream'],$appender['mode']);							
						
						
						break;
					case 'Zend_Log_Writer_Db':
						
						if ( !Zend_Registry::isRegistered('database') ) throw new Zend_Log_Exception('Zend_Log_Writer_Db required avaible database');
						if ( !isset($appender['tablename']) ) throw new Zend_Log_Exception('Zend_Log_Writer_Db required tablename params in config.ini');
						
						Zend_Loader::loadClass('Zend_Log_Writer_Db');
						$db = Zend_Registry::get('database');
						$l = new $appender['type']($db,$appender['tablename']);
						
						break;
					default:
						throw new Zend_Log_Exception('Type of appender INVALID');
						break;
				}
				
				switch ($appender['level']) {
					case 'DEBUG':
						$l->addFilter(new Zend_Log_Filter_Priority(Zend_Log::DEBUG));
						break;
					case 'WARN':
						$l->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN));
						break;
					case 'ERROR':
						$l->addFilter(new Zend_Log_Filter_Priority(Zend_Log::ERR));
						break;
					case 'AUDIT':
						$l->addFilter(new Zend_Log_Filter_Priority(Sigma_Log::AUDIT,'='));
						break;					
					default:
						$l->addFilter(new Zend_Log_Filter_Priority(Zend_Log::ERR));
						throw new Zend_Log_Exception('Sigma_log required valid appender level params in config.ini; not valid : '.$appender['level']);
					break;
				}
				
				
				if ( isset($appender['formatter']) ) {
					
					switch ($appender['formatter']) {
						case 'Zend_Log_Formatter_Xml':
							
							if ( !isset($appender['fields']) ) throw new Zend_Log_Exception('Zend_Log_Formatter_Xml required fields separated from "," params in config.ini');
							
							Zend_Loader::loadClass('Zend_Log_Formatter_Xml');
							
							$chunk = split(",",$appender['fields']);

							$formatter = new Zend_Log_Formatter_Xml($appender_name);
							$l->setFormatter($formatter);
							
							break;
						case 'Zend_Log_Formatter_Simple':
							
							if ( !isset($appender['style']) ) throw new Zend_Log_Exception('Zend_Log_Formatter_Simple required style params in config.ini');
							
							Zend_Loader::loadClass('Zend_Log_Formatter_Simple');
							$formatter = new Zend_Log_Formatter_Simple($appender['style']. PHP_EOL);
							$l->setFormatter($formatter);
							
							break;
						default:
							break;
					}
					
				}
				$this->addWriter($l);

			}

            unset($options['appender']);
        }
		
		return $this;
	}
	
	 /**
     * Set log state from config object
     * 
     * @param  Zend_Config $config 
     * @return Sigma_Log
     */
	public function setConfig(Zend_Config $config){
		return $this->setOptions($config->toArray());
	}
	
	/**
	 * Log Info Message 
	 */
	public function info($message){
		$this->log($message,Zend_Log::INFO);
	}
	
	/**
	 * Log Error Message 
	 */
	public function error($message){
		$this->log($message,Zend_Log::ERR);
	}
	
	/**
	 * Log Warinig Message 
	 */
	public function warn($message){
		$this->log($message,Zend_Log::WARN);
	}
	
	/**
	 * Log Debug Message 
	 */
	public function debug($message){
		$this->log($message,Zend_Log::DEBUG);
	}
	
	/**
	 * Log Audit Message 
	 */
	public function audit($message){
		$this->log($message,Sigma_Log::AUDIT);
	}
	
}

?>