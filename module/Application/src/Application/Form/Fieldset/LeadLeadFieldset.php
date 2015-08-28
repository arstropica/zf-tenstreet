<?php
namespace Application\Form\Fieldset;
use Application\Entity\Lead;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class LeadLeadFieldset extends Fieldset implements InputFilterProviderInterface
{

	protected $_fieldNames;

	public function __construct ()
	{
		parent::__construct('lead');
		
		$this->setHydrator(new ClassMethodsHydrator(false))->setObject(
				new Lead());
		
		$this->_fieldNames = self::getFieldNames();
		
		foreach ($this->_fieldNames as $label => $fieldName) {
			switch ($fieldName) {
				case 'id':
				case 'driverid':
				case 'formid':
				case 'submitted':
					$this->add(
							array(
									'options' => array(
											'label' => $label,
											'label_attributes' => array(
													'class' => 'sr-only'
											)
									),
									'type' => 'hidden',
									'allow_empty' => true,
									'name' => $fieldName
							));
					break;
				case 'timecreated':
					$this->add(
							array(
									'options' => array(
											'label' => $label,
											'format' => 'Y-m-d\TH:i'
									),
									'type' => '\Zend\Form\Element\DateTimeLocal',
									'name' => $fieldName
							));
					break;
				case 'timesubmitted':
					$this->add(
							array(
									'options' => array(
											'label' => $label,
											'format' => 'Y-m-d\TH:i'
									),
									'type' => '\Zend\Form\Element\DateTimeLocal',
									'allow_empty' => true,
									'name' => $fieldName
							));
					break;
				case 'referrer':
					$this->add(
							array(
									'options' => array(
											'label' => $label
									),
									'type' => '\Zend\Form\Element\Url',
									'name' => $fieldName
							));
					break;
				case 'lastresponse':
					$this->add(
							array(
									'options' => array(
											'label' => $label
									),
									'type' => 'textarea',
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
				case 'submitted':
				case 'formid':
				case 'id':
				case 'driverid':
					$specs[$fieldName] = array(
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Int'
									)
							)
					);
					break;
				case 'timecreated':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Date',
											'options' => array(
													'format' => 'Y-m-d\TH:i',
													'step' => 'any',
													'locale' => 'en',
													'messages' => array(
															\Zend\Validator\Date::INVALID => 'This seems to be an invalid date value.',
															\Zend\Validator\Date::INVALID_DATE => 'This seems to be an invalid date.',
															\Zend\Validator\Date::FALSEFORMAT => 'The date is not in the right format.'
													)
											)
									),
									array(
											'name' => 'NotEmpty',
											'options' => array(
													'messages' => array(
															\Zend\Validator\NotEmpty::IS_EMPTY => 'Please provide a date.'
													)
											)
									)
							)
					);
					break;
				case 'timesubmitted':
					$specs[$fieldName] = array(
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'validators' => array(
									array(
											'name' => 'Date',
											'options' => array(
													'format' => 'Y-m-d\TH:i',
													'step' => 'any',
													'locale' => 'en',
													'messages' => array(
															\Zend\Validator\Date::INVALID => 'This seems to be an invalid date value.',
															\Zend\Validator\Date::INVALID_DATE => 'This seems to be an invalid date.',
															\Zend\Validator\Date::FALSEFORMAT => 'The date is not in the right format.'
													)
											)
									)
							)
					);
					break;
				case 'lastresponse':
				case 'company':
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
													'min' => 3,
													'max' => 1000
											)
									)
							)
					);
					break;
				case 'formname':
				case 'referrer':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							)
					);
					break;
				case 'ipaddress':
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
													'min' => 7,
													'max' => 15
											)
									)
							)
					);
					break;
				case 'companyid':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Int'
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
				"Lead ID" => "id",
				"Form ID" => "formid",
				"Company ID" => "companyid",
				"Company" => "company",
				"Visitor IP Address" => "ipaddress",
				"Referrer" => "referrer",
				"Form Name" => "formname",
				"Time Created" => "timecreated",
				"Time Submitted" => "timesubmitted",
				"Submitted?" => "submitted",
				"Driver ID" => "driverid",
				"API Response" => "lastresponse"
		);
	}
}