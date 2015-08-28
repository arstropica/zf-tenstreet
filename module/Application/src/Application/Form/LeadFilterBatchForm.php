<?php
namespace Application\Form;
use Zend\Form\Form;

class LeadFilterBatchForm extends Form
{

	public function __construct ($name = null, $options = array())
	{
		parent::__construct($name, $options);
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Batch Submit',
								'id' => 'batchsubmitbutton',
								'class' => 'btn btn-primary'
						)
				));
		$this->setInputFilter(new LeadFilterBatchFormFilter());
	}
}