<?php
namespace Application\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Application\Form\Fieldset\LeadImportFieldset;

class LeadImportForm extends Form
{

	public function __construct ($name = null)
	{
		// we want to ignore the name passed
		parent::__construct('leadimport');
		
		$this->setAttribute('method', 'post')
			->setAttribute('enctype', 'multipart/form-data')
			->setHydrator(new ClassMethodsHydrator(false));
		
		$this->add(
				array(
						'name' => 'leadTmpFile',
						'attributes' => array(
								'type' => 'hidden'
						)
				));
		
		$csrf = new Element\Csrf('csrf');
		$this->add($csrf);
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Upload',
								'id' => 'submit',
								'class' => 'btn btn-primary'
						)
				));
	}

	public function addConfirmField ()
	{
		$this->add(
				array(
						'name' => 'confirm',
						'attributes' => array(
								'value' => 1,
								'type' => 'hidden'
						),
						'options' => array(
								'label' => 'Confirm Import'
						)
				));
	}

	public function addUploadField ()
	{
		$this->add(
				array(
						'name' => 'leadsUpload',
						'attributes' => array(
								'type' => 'file',
								'id' => 'leads-upload'
						),
						'options' => array(
								'label' => 'Upload Leads'
						)
				));
		$this->addInputFilter();
	}

	public function addImportFieldset ($options = array())
	{
		$leadImportFieldset = new LeadImportFieldset($options);
		$leadImportFieldset->setName('match')->setOptions(
				array(
						'label' => "Enter or Select matching fields for your data.",
						'use_as_base_fieldset' => false
				));
		$this->add($leadImportFieldset);
		return $leadImportFieldset;
	}

	public function addSubmissionsFieldset ($name = 'submissions', $submissions = array())
	{
		$submissionsFieldset = new Fieldset($name, 
				[
						'use_as_base_fieldset' => false
				]);
		
		if ($submissions) {
			foreach ($submissions as $i => $submission) {
				foreach ($submission as $field => $value) {
					$submissionsFieldset->add(
							array(
									'name' => "{$i}[{$field}]",
									'attributes' => array(
											'value' => $value,
											'type' => 'hidden'
									)
							));
				}
			}
		}
		
		$this->add($submissionsFieldset);
		return $submissionsFieldset;
	}

	public function getImportFieldset ()
	{
		return new LeadImportFieldset();
	}

	public function addInputFilter ()
	{
		$inputFilter = new InputFilter();
		$factory = new InputFactory();
		
		$inputFilter->add(
				$factory->createInput(
						array(
								'name' => 'leadsUpload',
								'required' => true
						)));
		
		$this->setInputFilter($inputFilter);
	}
}