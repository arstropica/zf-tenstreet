<?php
namespace Application\Form;
use Zend\InputFilter\InputFilter;

class LeadFilterFormFilter extends InputFilter
{

	public function __construct ()
	{
		$this->add(
				array(
						'name' => 'daterange',
						'required' => false,
						'filters' => array(
								array(
										'name' => 'StringTrim'
								)
						)
				));

		$this->add(
				array(
						'name' => 'status',
						'allow_empty' => true,
						'required' => false,
						'filters' => array(
								array(
										'name' => 'StripTags'
								),
								array(
										'name' => 'StringTrim'
								)
						)
				));
		
		$this->add(
				array(
						'name' => 'sites',
						'allow_empty' => true,
						'required' => false,
						'filters' => array(
								array(
										'name' => 'StripTags'
								),
								array(
										'name' => 'StringTrim'
								)
						)
				));
	}
}