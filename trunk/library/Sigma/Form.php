<?php

/**
 * Enter description here...
 * @see http://framework.zend.com/manual/en/zend.form.standardElements.html
 */
class Sigma_Form extends Zend_Form
{
	
	public function __construct($name,$options = null){

		if (is_null($options)) {
			$iniForm = Zend_Registry::get('formsConfig');
		} else {
			$iniForm = $options;
		}

		parent::__construct($iniForm->user->$name);
		
	}
	
    public function init()
    {

		// contro il CSRF metto un hash
		$this->addElement('hash', 'no_csrf', array('salt' => 'unique'));

		$this->clearDecorators();

        $this->addDecorator('FormElements')
        ->addDecorator('HtmlTag', array('tag' => 'fieldset','class' => 'sigma_form'))
        ->addDecorator('Description',array('placement' => 'prepend'))
        ->addDecorator('Form');
        
        $this->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors'),
            array('Description'),
            array('Label', array('separator'=>' ')),
            array('HtmlTag', array('tag' => 'div', 'class'=>'element-group')),
        ));
        
        // buttons do not need labels
        $this->submit->setDecorators(array(
            array('ViewHelper'),
            array('Description'),
            array('HtmlTag', array('tag' => 'div', 'class'=>'submit-group')),
        ));

        
        $this->no_csrf->setDecorators(array(
            array('ViewHelper'),
            array('Description'),
            array('HtmlTag', array('tag' => 'span', 'class'=>'hidden')),
        ));

    }
}

?>
