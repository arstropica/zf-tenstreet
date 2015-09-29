<?php
namespace Application\Mapper;
use Zend\Db\Adapter\Adapter;
use Application\Entity\Submission as SubmissionEntity;
use Application\Entity\Lead as LeadEntity;
use Application\Entity\Form as FormEntity;
use Application\Entity\Detail as DetailEntity;
use Application\Mapper\LeadMapper;
use Application\Mapper\FormMapper;
use Application\Mapper\DetailMapper;
use Application\Hydrator\DetailHydrator;
use Application\Hydrator\LeadHydrator;
use Application\Hydrator\FormHydrator;
use Zend\ServiceManager\ServiceManager;

class SubmissionMapper
{

	protected $dbAdapter;

	protected $leadTable;

	protected $sm;

	public function __construct (ServiceManager $sm, Adapter $dbAdapter)
	{
		$this->sm = $sm;
		
		$this->leadTable = $this->sm->get('Application\Model\LeadTable');
		
		$this->dbAdapter = $dbAdapter;
	}

	public function fetchAll ($paging = false, $sort = "timecreated", $order = "DESC", 
			$filters = array())
	{
		$results = array();
		$leadMapper = new LeadMapper($this->dbAdapter);
		$formMapper = new FormMapper($this->dbAdapter);
		$detailMapper = new DetailMapper($this->dbAdapter);
		$leads = $leadMapper->fetchAll($paging, $sort, $order, $filters);
		foreach ($leads as $lead) {
			$submission = new SubmissionEntity();
			$form = $formMapper->getForm($lead->getFormid());
			$detail = $detailMapper->getDetail($lead->getId());
			
			$submission->setLead($lead);
			$submission->setForm($form);
			$submission->setDetail($detail);
			$results[] = $submission;
		}
		
		return $results;
	}

	public function getSubmission ($id)
	{
		$submission = new SubmissionEntity();
		
		$leadMapper = new LeadMapper($this->dbAdapter);
		$formMapper = new FormMapper($this->dbAdapter);
		$detailMapper = new DetailMapper($this->dbAdapter);
		
		$lead = $leadMapper->getLead($id);
		
		if ($lead) {
			$submission->setId($id);
			$submission->setLead($lead);
			$form = $formMapper->getForm($lead->getFormid());
			$submission->setForm($form);
			$detail = $detailMapper->getDetail($id);
			$submission->setDetail($detail);
		}
		
		return $submission;
	}

	public function saveSubmission (SubmissionEntity $submission)
	{
		return $this->leadTable->saveLead($submission);
	}

	public function extract (SubmissionEntity $submission, $structure = 'flat')
	{
		$result = array();
		$leadHydrator = new LeadHydrator();
		$formHydrator = new FormHydrator();
		$detailHydrator = new DetailHydrator();
		
		$lead = $leadHydrator->extract($submission->getLead());
		$form = $formHydrator->extract($submission->getForm());
		$detail = $detailHydrator->extract($submission->getDetail());
		
		if ($structure == 'flat') {
			foreach ($lead as $name => $value) {
				$result['lead[' . $name . ']'] = $value;
			}
			foreach ($form as $name => $value) {
				$result['form[' . $name . ']'] = $value;
			}
			foreach ($detail as $name => $value) {
				$result['detail[' . $name . ']'] = $value;
			}
		} else {
			$result['lead'] = $lead;
			$result['form'] = $form;
			$result['detail'] = $detail;
		}
		
		return $result;
	}

	public function hydrate ($array, $structure = 'flat')
	{
		$submission = new SubmissionEntity();
		$data = array(
				'lead' => array(),
				'form' => array(),
				'detail' => array()
		);
		
		$leadHydrator = new LeadHydrator();
		$formHydrator = new FormHydrator();
		$detailHydrator = new DetailHydrator();
		
		if ($structure == 'flat') {
			$i = 0;
			foreach ($array as $field => $value) {
				switch ($field) {
					case "company":
					case "formname":
					case "ipaddress":
					case "referrer":
					case "timecreated":
						$data['lead'][$field] = $value;
						break;
					case "FirstName":
					case "LastName":
					case "City":
					case "State":
					case "Email":
					case "Phone":
					case "Question1":
					case "Answer1":
					case "Question2":
					case "Answer2":
					case "Question3":
					case "Answer3":
						$data['detail'][$i]['attributename'] = $field;
						$data['detail'][$i]['value'] = $value;
						break;
				}
				$i ++;
			}
		} else {
			$_detail = $array['detail'];
			$array['detail'] = [];
			$i = 0;
			foreach ($_detail as $field => $value) {
				$array['detail'][$i]['attributename'] = $field;
				$array['detail'][$i]['value'] = $value;
				$i ++;
			}
			$data = $array;
		}
		
		$lead = $leadHydrator->hydrate($data['lead'], new LeadEntity());
		$form = $data['form'] ? $formHydrator->hydrate($data['form'], 
				new FormEntity()) : new FormEntity();
		$detail = $detailHydrator->hydrate($data['detail'], new DetailEntity());
		
		$submission->setLead($lead);
		$submission->setForm($form);
		$submission->setDetail($detail);
		
		return $submission;
	}

	public function getFields (SubmissionEntity $submission, $structure = 'flat')
	{
		$result = array();
		$lead = $submission->getLead()->getArrayCopy();
		$form = $submission->getForm()->getArrayCopy();
		$detail = $submission->getDetail()->getArrayCopy();
		
		unset($lead['array_copy']);
		unset($form['array_copy']);
		unset($detail['array_copy']);
		
		if ($structure == 'flat') {
			foreach ($lead as $name => $value) {
				$result[] = "lead[" . $name . "]";
			}
			foreach ($form as $name => $value) {
				$result[] = "form[" . $name . "]";
			}
			foreach ($detail as $name => $value) {
				$result[] = "detail[" . $name . "]";
			}
		} else {
			$result['lead'] = array_keys($lead);
			$result['form'] = array_keys($form);
			$result['detail'] = array_keys($detail);
		}
		
		return $result;
	}
}
