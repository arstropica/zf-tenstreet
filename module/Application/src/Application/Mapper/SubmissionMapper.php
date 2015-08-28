<?php
namespace Application\Mapper;
use Zend\Db\Adapter\Adapter;
use Application\Entity\Submission as SubmissionEntity;
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

	public function fetchAll ()
	{
		$results = array();
		$leadMapper = new LeadMapper($this->dbAdapter);
		$formMapper = new FormMapper($this->dbAdapter);
		$detailMapper = new DetailMapper($this->dbAdapter);
		$leads = $leadMapper->fetchAll();
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
}
