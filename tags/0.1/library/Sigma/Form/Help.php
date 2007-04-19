<?php

class Sigma_Form_Help {
	
	/**
     * Singleton instance
     *
     * @var Sigma_Form_Help
     */
    protected static $_instance = null;
    
    protected $flow = null;
    
    private $lenght = 8;
	
    /**
     * Returns an instance of Sigma_Form_Help
     *
     * Singleton pattern implementation
     *
     * @return Sigma_Form_Help Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
	
	private function __construct(){
		
		$this->flow = new Zend_Session_Namespace('Sigma_Flow');

		//$this->flo->before_page = $sigma_flow->last_page;	// è la pagina visitata precedentemente
		//$this->flo->last_page = $_SERVER["REQUEST_URI"];	// è la pagina corrente (ossia l'ultima)

	}
	
	/**
	 * Create a random Token number+lecter [0-9][a-z]
	 * @return string token
	 */
	public function randToken(){
		
		$token = '';
		
		for ( $i = 0; $i < $this->lenght; $i++ ){
			$token .= $this->rand();
		}
		
		return $token;
		
	}
	
	/**
	 * Return a random number or lecter [0-9][a-z]
	 * @return string number or lecter 
	 */
	private function rand(){
		
		$p = rand(1,2);
		
		$n = rand(141,172);

		while ( $n == 148 || $n == 149 || $n == 158 || $n == 159 || $n == 168 || $n == 169 ) {
			$n = rand(141,172);
		}
		
		if ( 1 == $p ) {
			// lettera
			$oct = octdec($n);
			return chr($oct);
		} else {
			// numero
			return rand(0,10); 
		}
		
	}
	
}
	
?>