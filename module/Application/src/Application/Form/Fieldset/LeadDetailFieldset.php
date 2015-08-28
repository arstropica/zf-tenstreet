<?php
namespace Application\Form\Fieldset;
use Application\Entity\Detail;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class LeadDetailFieldset extends Fieldset implements 
		InputFilterProviderInterface
{

	protected $_fieldNames;

	public function __construct ()
	{
		parent::__construct('detail');
		
		$this->setHydrator(new ClassMethodsHydrator(false))->setObject(
				new Detail());
		
		$this->_fieldNames = self::getFieldNames();
		
		foreach ($this->_fieldNames as $label => $fieldName) {
			switch ($fieldName) {
				case 'Email':
					$this->add(
							array(
									'options' => array(
											'label' => $label
									),
									'type' => '\Zend\Form\Element\Email',
									'name' => $fieldName
							));
					break;
				default:
					$this->add(
							array(
									'options' => array(
											'label' => $label
									),
									'type' => 'text',
									'name' => $fieldName
							));
					break;
			}
		}
	}

	public function getInputFilterSpecification ()
	{
		$specs = array();
		if (! $this->_fieldNames) {
			$this->_fieldNames = $this->getFieldNames();
		}
		
		foreach ($this->_fieldNames as $fieldName) {
			switch ($fieldName) {
				case 'FirstName':
				case 'LastName':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Zend\Validator\StringLength',
											'options' => array(
													'min' => 3,
													'max' => 100
											)
									)
							)
					);
					break;
				case 'City':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Zend\Validator\StringLength',
											'options' => array(
													'min' => 2,
													'max' => 100
											)
									)
							)
					);
					break;
				case 'State':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Zend\Validator\StringLength',
											'options' => array(
													'min' => 2,
													'max' => 100
											)
									)
							)
					);
					break;
				case 'Phone':
					$specs[$fieldName] = array(
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Zend\Validator\StringLength',
											'options' => array(
													'min' => 9,
													'max' => 15
											)
									)
							)
					);
					break;
				case 'Email':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									new Validator\EmailAddress()
							)
					);
					break;
				case 'Question1':
				case 'Answer1':
				case 'Question2':
				case 'Answer2':
				case 'Question3':
				case 'Answer3':
					$specs[$fieldName] = array(
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Zend\Validator\StringLength',
											'options' => array(
													'min' => 0,
													'max' => 1000
											)
									)
							)
					);
					break;
			}
		}
		return $specs;
	}

	public static function getFieldNames ()
	{
		return array(
				"First Name" => "FirstName",
				"Last Name" => "LastName",
				"City" => "City",
				"State" => "State",
				"Email" => "Email",
				"Phone" => "Phone",
				"Question 1" => "Question1",
				"Answer 1" => "Answer1",
				"Question 2" => "Question2",
				"Answer 2" => "Answer2",
				"Question 3" => "Question3",
				"Answer 3" => "Answer3"
		);
	}
}