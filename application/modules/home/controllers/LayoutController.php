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
 * @package    Sigma_Controller
 * @copyright  Copyright (c) 2007 Stefano Tamagnini
 * @author	   Stefano Tamagnini
 * @license    New BSD License
 */


/**
 * @category	Sigma
 * @package 	Sigma_Controller
 * @copyright	Copyright (c) 2007 Stefano Tamagnini
 * @license		New BSD License
 */
class Home_LayoutController extends Sigma_Controller_Action {
	
	
	function menuAction(){
		
		Zend_Loader::loadClass('Modules',BASE_DIRECTORY.'/application/models/tables/');
		
		$modules = new Modules();
		$modules_array = $modules->fetchAllActive();
		
		$this->view->modules = array();
		
		foreach($modules_array as $module_row) {
			//$this->addControllerDirectory(BASE_DIRECTORY.'/application/modules/'.$mod->path_name,$mod->name);
			//$this->view->modules = array("modules_name" => "modules_link");
			$this->view->modules[$module_row->description] = $module_row->name;
		}

	}
	
}

?>