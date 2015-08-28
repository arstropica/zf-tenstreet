<?php
namespace Application\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class LeadForm extends Form
{

	public function __construct ($name = null)
	{
		// we want to ignore the name passed
		parent::__construct('lead');
		
		$this->setAttribute('method', 'post')
			->setHydrator(new ClassMethodsHydrator(false))
			->setInputFilter(new InputFilter());
		
		$this->add(
				array(
						'type' => 'Application\Form\Fieldset\LeadLeadFieldset',
						'options' => array(
								'label' => "Lead Info",
								'use_as_base_fieldset' => false
						)
				));
		$this->add(
				array(
						'type' => 'Application\Form\Fieldset\LeadFormFieldset',
						'options' => array(
								'label' => "Lead Form Info",
								'use_as_base_fieldset' => false
						)
				));
		$this->add(
				array(
						'type' => 'Application\Form\Fieldset\LeadDetailFieldset',
						'options' => array(
								'label' => "Lead Details",
								'use_as_base_fieldset' => false
						)
				));
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Submit',
								'id' => 'submit',
								'class' => 'btn btn-primary'
						)
				));
	}
}