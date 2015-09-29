<?php
namespace Application\Form\Fieldset;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class LeadImportFieldset extends Fieldset implements 
		InputFilterProviderInterface
{

	protected $_fieldNames;

	public function __construct ($fields = array())
	{
		parent::__construct('form');
		
		$this->_fieldNames = self::getFieldNames();
		
		foreach ($this->_fieldNames as $label => $fieldName) {
			switch ($fieldName) {
				case 'company':
				case 'formname':
					$this->add(
							array(
									'options' => array(
											'label' => $label
									),
									'required' => true,
									'type' => 'text',
									'name' => $fieldName
							));
					break;
				case "ipaddress":
				case "referrer":
				case "timecreated":
				case "FirstName":
				case "LastName":
				case "City":
				case "State":
				case "Email":
				case "Phone":
					$this->add(
							array(
									'name' => $fieldName,
									'type' => 'Zend\Form\Element\Select',
									'required' => true,
									'options' => array(
											'label' => $label,
											'label_attributes' => array(
													'class' => 'sr-only'
											),
											'value_options' => $fields,
											'empty_option' => 'Choose ' . $label .
													 ' Field'
									),
									'attributes' => array(
											'id' => $fieldName
									)
							));
					break;
				default:
					$this->add(
							array(
									'name' => $fieldName,
									'type' => 'Zend\Form\Element\Select',
									'options' => array(
											'label' => $label,
											'label_attributes' => array(
													'class' => 'sr-only'
											),
											'value_options' => $fields,
											'empty_option' => 'Choose ' . $label .
													 ' Field'
									),
									'attributes' => array(
											'id' => $fieldName
									)
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
				case "company":
				case "formname":
				case "FirstName":
				case "LastName":
				case "City":
				case "State":
				case "Email":
				case "Phone":
				case "ipaddress":
				case "referrer":
				case "timecreated":
					$specs[$fieldName] = array(
							'required' => true
					);
					break;
				case "Question1":
				case "Question2":
				case "Question3":
					$specs[$fieldName] = array(
							'required' => false
					);
					break;
			}
		}
		return $specs;
	}

	public static function getFieldNames ()
	{
		return array(
				"Company" => "company",
				"Form Name" => "formname",
				"First Name" => "FirstName",
				"Last Name" => "LastName",
				"City" => "City",
				"State" => "State",
				"Email" => "Email",
				"Phone" => "Phone",
				"IP Address" => "ipaddress",
				"Referrer" => "referrer",
				"Time Created" => "timecreated",
				"Question 1" => "Question1",
				"Question 2" => "Question2",
				"Question 3" => "Question3"
		);
	}

	public static function getStructuredFieldNames ()
	{
		return array(
				"text" => array(
						"required" => array(
								"Company" => "company",
								"Form Name" => "formname"
						)
				),
				"select" => array(
						"required" => array(
								"First Name" => "FirstName",
								"Last Name" => "LastName",
								"City" => "City",
								"State" => "State",
								"Email" => "Email",
								"Phone" => "Phone",
								"IP Address" => "ipaddress",
								"Referrer" => "referrer",
								"Time Created" => "timecreated"
						),
						"optional" => array(
								"Question 1" => "Question1",
								"Question 2" => "Question2",
								"Question 3" => "Question3"
						)
				)
		);
	}
}