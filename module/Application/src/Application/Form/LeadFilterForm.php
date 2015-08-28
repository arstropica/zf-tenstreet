<?php
namespace Application\Form;
use Zend\Form\Form;

class LeadFilterForm extends Form
{

	public function init ()
	{
		$this->setAttribute('method', 'GET');
		$this->add(
				array(
						'name' => 'sites',
						'type' => 'SourceSelect'
				));
		$this->add(
				array(
						'name' => 'status',
						'type' => 'Zend\Form\Element\Select',
						'options' => array(
								'label' => 'Filter Status',
								'label_attributes' => array(
										'class' => 'sr-only'
								),
								'value_options' => array(
										'1' => 'Submitted',
										'0' => 'Not Submitted'
								),
								'empty_option' => 'All Statuses'
						),
						'attributes' => array(
								'id' => 'status'
						)
				));
		$this->add(
				array(
						'name' => 'daterange',
						'type' => 'Application\Form\Element\DateRange',
						'options' => array(
								'label' => 'Date Range',
								'label_attributes' => array(
										'class' => 'sr-only'
								)
						),
						'attributes' => array(
								'id' => 'daterange'
						)
				));
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Filter',
								'id' => 'submitbutton'
						)
				));
		$this->setInputFilter(new LeadFilterFormFilter());
	}
}