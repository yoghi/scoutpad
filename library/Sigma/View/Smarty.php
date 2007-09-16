<?php
/**
 * 
 */

class Sigma_View_Smarty extends Zend_View_Abstract
{
	/**
	 * Oggetto Smarty 
	 * 
	 * @var Smarty
	 */
	protected $_smarty;

	/**
	 * Costruttore
	 * Es:
	 * $view = new SmartyView(array('compileDir' => './template_c'));
	 * 
	 * 
	 * @param array $config parametri di configurazione
	 */
	public function __construct($config = array())
	{
		$this->_smarty = new Smarty();

		if(!isset($config['compileDir']))
		throw new Exception('compileDir is not set for '.get_class($this));
		else
		$this->_smarty->compile_dir = $config['compileDir'];

		if(isset($config['configDir']))
		$this->_smarty->config_dir = $config['configDir'];

		if(isset($config['pluginDir']))
		$this->_smarty->plugin_dir[] = $config['pluginDir'];

		parent::__construct($config);
	}


	public function getEngine()
	{
		return $this->_smarty;
	}

	public function __set($key,$val)
	{
		$this->_smarty->assign($key,$val);
	}

	public function __isset($key)
	{
		$var = $this->_smarty->get_template_vars($key);
		if($var)
		return true;

		return false;
	}

	public function __unset($key)
	{
		$this->_smarty->clear_assign($key);
	}

	public function assign($spec,$value = null)
	{
		if($value === null)
		$this->_smarty->assign($spec);
		else
		$this->_smarty->assign($spec,$value);
	}


	public function clearVars()
	{
		$this->_smarty->clear_all_assign();
	}

	protected function _run()
	{
		$this->strictVars(true);

		$this->_smarty->assign_by_ref('this',$this);

		$templateDirs = $this->getScriptPaths();

		$file = substr(func_get_arg(0),strlen($templateDirs[0]));
		$this->_smarty->template_dir = $templateDirs[0];

		echo $this->_smarty->fetch($file);
	}
}
?>