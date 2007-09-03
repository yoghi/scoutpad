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
 * @package    Sigma_Acl
 * @copyright  Copyright (c) 2007 Stefano Tamagnini 
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */
 

/**
 * Sigma_Acl_Permission
 * 
 * E' un sistema puntuale vince la regola piu precisa 
 * 
 * ES: 	admin,permessi vince su admin,null
 * 
 * @category	Sigma
 * @package 	Sigma_Acl
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 * @version		0.0.2 - 2007 agosto 31 - 11:12 - Stefano Tamagnini  
 */
class Sigma_Acl_Permission {
	
	const READ  	= 'R'; // puo solo leggere
	const WRITE  	= 'W'; // or modify , implica R
	const EXECUTE 	= 'E'; // implica R e W , aggiunge/elimina/etc...
	const NOTREAD	= 'N'; // non puo fare nulla, gli è vietato tutto
	
	/**
	 * Costruttore Sigma_Acl_Permission
	 *
	 * @param integer $user_id identificativo dell'utente di cui si necessitano i permessi
	 */
	public function __construct(){
		Zend_Loader::loadClass('Permission','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
	}
	
	/**
	 * Verifico se ho un dato permesso
	 *
	 * @param integer $user_id id utente da controllare
	 * @param string $modulo modulo su cui è richiesto il permesso
	 * @param string $controller controller su cui e' richiesto il permesso
	 * @param char $permesso permesso da controllare
	 */
	public function hasPermission($user_id,$modulo,$controller,$permesso){
		
		$controller_name = is_null($controller) ? 'NULL' : $controller;
		
		Zend_Registry::get('log')->log('Controllo il permesso '.$permesso.' dell\'utente '.$user_id.' sul '.$modulo.'=>'.$controller_name ,Zend_Log::DEBUG);
		
		if( $this->checkPermission($permesso) ){
			
			$permission_db = new Permission();
			
			$where = array();
			$where[] = 'User = '.$permission_db->getAdapter()->quote($user_id);
			$where[] = 'Modulo = '.$permission_db->getAdapter()->quote($modulo);
			if (is_null($controller))  $where[] = 'Controller IS NULL ';
			else $where[] = 'Controller = '.$permission_db->getAdapter()->quote($controller);
			
			$p = $permission_db->fetchAll($where)->toArray();
		
			if ( count($p) == 0  ) {

				if ( $controller !== null ) {
					return $this->hasPermission($user_id,$modulo,null,$permesso);
				}
				
				if ( strtoupper( $permesso ) == 'R'  ) return true; //posso sempre leggere se non diversamente specificato
				return false;
			}

			$permesso_db = $p[0]['Permission']; //strtoupper

			if ( $permesso_db == 'N' && strtoupper( $permesso ) == 'N' ) return true;
			
			if ( $permesso_db == 'N' ) return false;
			
			if ( $permesso_db == strtoupper( $permesso ) ) return true;
			
			if ( $permesso_db == 'E' ) return true;
			
			if ( strtoupper( $permesso ) == 'E'  ) return false;
			
			if ( $permesso_db == 'W' ) return true; // ho gia escluso il caso W e E

			if ( strtoupper( $permesso ) == 'W'  ) return false;
			
			// rimane solo read quindi è falso sempre! (infatti è NONREAD!)
			
			return false;
			
		} else return false;
		
	}
	
	/**
	 * Verifico se è un permesso valido
	 *
	 * @param char $permessi
	 * @return boolean
	 */
	protected function checkPermission($permessi){
		
		if ( trim($permessi) == '' ) return false;
		
		$p = explode(',',trim($permessi));
		
		foreach($p as $permesso){
			
			switch ($permesso) {
				case self::READ :
				case self::NOTREAD :
				case self::WRITE :
				case self::EXECUTE :
					break;
				default:
					Zend_Registry::get('log')->log('Permesso non conosciuto : '.$permesso,Zend_Log::DEBUG);
					return false;
				break;
			}
			
		}
		
		return true;
		
	}
	
}

?>