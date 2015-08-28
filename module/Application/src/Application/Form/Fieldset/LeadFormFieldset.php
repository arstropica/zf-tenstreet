<?php
namespace Application\Form\Fieldset;
use Application\Entity\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class LeadFormFieldset extends Fieldset implements InputFilterProviderInterface
{

	protected $_fieldNames;

	public function __construct ()
	{
		parent::__construct('form');
		
		$this->setHydrator(new ClassMethodsHydrator(false))->setObject(
				new Form());
		
		$this->_fieldNames = self::getFieldNames();
				
		foreach ($this->_fieldNames as $label => $fieldName) {
			switch ($fieldName) {
				case 'id':
				case 'companyid':
					$this->add(
							array(
									'options' => array(
											'label' => $label,
											'label_attributes' => array(
													'class' => 'sr-only'
											)
									),
									'allow_empty' => true,
									'type' => 'hidden',
									'name' => $fieldName
							));
					break;
				default:
					$this->add(
							array(
									'options' => array(
											'label' => $label,
											'label_attributes' => array(
													'class' => 'sr-only'
											)
									),
									'type' => 'hidden',
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
				case 'companyid':
				case 'id':
					$specs[$fieldName] = array(
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Int'
									)
							)
					);
					break;
				case 'form':
				case 'source':
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
													'min' => 0,
													'max' => 255
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
				"Form ID" => "id",
				"Form Source" => "source",
				"Form Name" => "form",
				"Company ID" => "companyid",
				"Company" => "company"
		);
	}
}