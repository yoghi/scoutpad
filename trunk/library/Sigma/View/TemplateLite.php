<?php
class Sigma_View_TemplateLite extends Zend_View_Abstract
{
	private $_tpl = false;
	private $_ctemplate = null;

	public function __construct($data = array())
	{
		parent::__construct($data);

		//$config = Zend::registry('config');
		$this->_tpl = new Template_Lite;
		
		$this->_tpl->template_dir = null;
		$this->_tpl->compile_dir = null;

		$this->_tpl->caching = false;
		
        if (array_key_exists('compile_dir', $data)) {
			$this->_tpl->compile_dir = $data['compile_dir'];
        } 
        
		if (array_key_exists('template_dir', $data)) {
			$this->_tpl->template_dir = $data['template_dir'];
        }

	}
	
	public function render($name){
		
		$l = $this->getScriptPaths();

		if ( is_null($this->_tpl->template_dir)  ) {
			$this->_tpl->template_dir = $l[0].'templates/';
        	//throw new Zend_View_Exception('Manca template_dir');
        }
        
		if ( is_null($this->_tpl->compile_dir)  ) {
			$this->_tpl->compile_dir = $l[0].'compiled/';
        	//throw new Zend_View_Exception('Manca compile_dir');
        }
		
		$this->_ctemplate = $name;
		ob_start();
        $this->_run($l[0].$this->_file); 

        //$this->addFilter(ob_get_clean()); // filter output
        
	}
	
    /**
     * Assign a variable to the template
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val)
    {
        $this->assign($key, $val);
    }
    
 	/**
     * Retrieve an assigned variable
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->_tpl->get_template_vars($key);
    }
    
    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        $value = $this->_tpl->get_template_vars($key);
        return null === $value;
    }
    
 	/**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or
     * property overloading ({@link __get()}/{@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_tpl->clear_all_assign();
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->_tpl->clear_assign($key);
    }

    
	/**
	 * @todo sistemare user_level
	 */
	protected function _run()
	{
		$auth_module = Zend::registry('auth_module');
		if ( $auth_module->isLoggedIn() ) {
			$identify = $auth_module->getToken()->getIdentity();
			$this->assign('user_name',$identify['nome']);
			$this->assign('user_surname',$identify['cognome']);
			$this->assign('user_role',$identify['role']);
		}
		$this->_tpl->display($this->_ctemplate);
	}

	public function assign($var, $value = null)
	{
		
		if (is_string($var))
		{
			if ( is_object($value)  ) {
    			$this->_tpl->assign_by_ref($var, $value);
    			Zend_Log::log('Setto oggetto:  '.$var, Zend_Log::LEVEL_DEBUG);
    		} else { 
				$this->_tpl->assign($var, $value);
			}
		}
		elseif (is_array($var))
		{
			foreach ($var as $key => $value)
			{
				if ( is_object($value)  ) {
	    			$this->_tpl->assign_by_ref($key, $value);
	    			Zend_Log::log('Setto oggetto:  '.$key, Zend_Log::LEVEL_DEBUG);
	    		} else { 
					$this->_tpl->assign($key, $value);
				}
			}
		}
		else { 
			throw new Zend_View_Exception('assign() expects a string or array, got '.gettype($var));
		}
	}

	public function escape($var)
	{
		if (is_string($var))
		{
			return parent::escape($var);
		}
		elseif (is_array($var))
		{
			foreach ($var as $key => $val)
			{
				$var[$key] = $this->escape($val);
			}

			return $var;
		}
		else
		{
			return $var;
		}
	}

	public function output($name)
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		header("Cache-Control: post-check=0, pre-check=0", FALSE);

		print parent::render($name);
	}

	public function isCached($template)
	{
		if ($this->_tpl->is_cached($template))
		{
			return true;
		}

		return false;
	}

	public function setCaching($caching)
	{
		$this->_tpl->caching = $caching;
	}


}

?>