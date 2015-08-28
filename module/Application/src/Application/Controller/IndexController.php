<?php
namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Form\LeadFilterBatchForm;
use Application\Form\LeadForm;
use Application\Entity\Submission as SubmissionEntity;
use Application\Entity\Lead as LeadEntity;
use Application\Entity\Form as FormEntity;
use Application\Entity\Detail as DetailEntity;
use Application\Controller\Plugin\ErrorResponse;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{

	protected $errorResponse;

	public function __construct ()
	{
		$this->errorResponse = new ErrorResponse($this);
	}

	public function indexAction ()
	{
		$sl = $this->getServiceLocator();
		
		$filters = $sl->get('FormElementManager')->get(
				'\Application\Form\LeadFilterForm');
		
		$page = (int) $this->params()->fromRoute('page') ?  : 1;
		$order = $this->params()->fromRoute('order') ?  : 'desc';
		$rorder = $order == 'desc' ? 'asc' : 'desc';
		$sort = $this->params()->fromRoute('sort') ?  : 'lead.id';
		$action = $this->params()->fromRoute('action') ?  : 'index';
		
		$fields = array();
		$fields['form.source'] = 'Referrer';
		$fields['timecreated'] = 'Created';
		$fields['submitted'] = 'Submitted';
		$fields['lastresponse'] = 'API Response';
		
		$request = $this->getRequest();
		$data = $request->getQuery();
		$fdata = array();
		
		if ($data) {
			$filters->setData($data);
			if (! $filters->isValid()) {
				$message = array(
						"Invalid Search Paramters."
				);
				$this->errorResponse->addMessages(null, $message, 
						$filters->getMessages());
				/*
				 * throw new \Exception(
				 * "Invalid Search Paramters: \n" .
				 * print_r($filters->getMessages(), true));
				 */
				$page = 1;
				$order = 'desc';
				$rorder = $order == 'desc' ? 'asc' : 'desc';
				$sort = 'lead.id';
			} else {
				$fdata = $filters->getData();
			}
		}
		
		// grab the paginator from the LeadTable
		$paginator = $this->getLeadMapper()->fetchAll(true, $sort, $order, 
				$fdata);
		// set the current page to what has been passed in query string, or to 1
		// if none set
		$paginator->setCurrentPageNumber($page);
		// set the number of items per page to 10
		$paginator->setItemCountPerPage(10);
		
		// Batch Form
		$batchForm = new LeadFilterBatchForm('leadbatchform');
		
		if (count($paginator) == 0) {
			$message = "No Results.";
			$this->errorResponse->addMessage($message, "info");
		}
		
		foreach ($paginator as $lead) {
			$cbx = new \Zend\Form\Element\Checkbox("sel[" . $lead->getId() . "]");
			if ($lead->getSubmitted()) {
				$cbx->setAttribute('disabled', 'disabled');
			}
			$batchForm->add($cbx);
		}
		
		return new ViewModel(
				array(
						'leads' => $paginator,
						'page' => $page,
						'order' => $order,
						'sort' => $sort,
						'rorder' => $rorder,
						'filters' => $filters,
						'fields' => $fields,
						'batchForm' => $batchForm,
						'query' => $data->toArray()
				));
	}

	public function viewAction ()
	{
		$submission = null;
		$form = new LeadForm();
		
		$id = (int) $this->params()->fromRoute('id', 0);
		if (! $id) {
			$message = "No Lead ID could be found.";
			$this->errorResponse->addMessage($message, "error");
			return $this->redirect()->toRoute('home', 
					array(
							'action' => 'index'
					), 
					array(
							'query' => array(
									'msg' => 1
							)
					));
		}
		
		// Get the Lead with the specified id. An exception is thrown
		// if it cannot be found, in which case go to the index page.
		try {
			$submission = $this->getSubmissionMapper()->getSubmission($id);
		} catch (\Exception $ex) {
			$message = "Lead Entry could not be retrieved.";
			$this->errorResponse->addMessage($message, "error");
			return $this->redirect()->toRoute('home', 
					array(
							'action' => 'index'
					), 
					array(
							'query' => array(
									'msg' => 1
							)
					));
		}
		
		if ($submission && $submission instanceof SubmissionEntity) {
			if (! $submission->getLead()) {
				$message = "A Lead could not be found with the ID: \"{$id}\". Return to <a href=\"javascript:history.back()\" class=\"alert-link\">previous page</a>?";
				$this->errorResponse->addMessage($message, "error");
			}
			$form->bind($submission);
			$form->get('lead')
				->get('timecreated')
				->setOptions(array(
					'format' => 'F d, Y H:i:s'
			));
			$form->get('lead')
				->get('timesubmitted')
				->setOptions(array(
					'format' => 'F d, Y H:i:s'
			));
			$form->remove('submit');
		} else {
			$message = "A Lead could not be found with the ID: \"{$id}\". Return to <a href=\"javascript:history.back()\" class=\"alert-link\">previous page</a>?";
			$this->errorResponse->addMessage($message, "error");
		}
		
		return new ViewModel(
				array(
						'submission' => $submission,
						'form' => $form
				));
	}

	public function addAction ()
	{
		$form = new LeadForm();
		$submission = new SubmissionEntity();
		$form->bind($submission);
		
		$request = $this->getRequest();
		$id = null;
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$id = $this->getSubmissionMapper()->saveSubmission($submission);
				
				// Redirect to Home
				$message = "Lead Added.";
				$this->errorResponse->addMessage($message, "success");
				return $this->redirect()->toRoute('home', 
						array(
								'action' => 'index'
						), 
						array(
								'query' => array(
										'msg' => 1
								)
						));
			} else {
				$message = array(
						"You have invalid Form Entries."
				);
				$this->errorResponse->addMessages(null, $message, 
						$form->getMessages());
				/*
				 * throw new \Exception(
				 * "Invalid Form Entries: \n" .
				 * print_r($form->getMessages(), true));
				 */
			}
		}
		
		return array(
				'form' => $form,
				'id' => $id
		);
	}

	public function editAction ()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		if (! $id) {
			$message = "No Lead ID could be found.";
			$this->errorResponse->addMessage($message, "error");
			return $this->redirect()->toRoute('home', 
					array(
							'action' => 'add'
					), 
					array(
							'query' => array(
									'msg' => 1
							)
					));
		}
		
		try {
			$submission = $this->getSubmissionMapper()->getSubmission($id);
		} catch (\Exception $ex) {
			$message = $ex->getMessage;
			$this->errorResponse->addMessage($message, "error");
			return $this->redirect()->toRoute('home', 
					array(
							'action' => 'index'
					), 
					array(
							'query' => array(
									'msg' => 1
							)
					));
		}
		
		$form = new LeadForm();
		if ($submission && $submission instanceof SubmissionEntity) {
			
			if (! $submission->getLead()) {
				$message = "A Lead could not be found with the ID: \"{$id}\". Return to <a href=\"javascript:history.back()\" class=\"alert-link\">previous page</a>?";
				$this->errorResponse->addMessage($message, "error");
			}
			
			// echo "<pre>" . print_r($submission, true) . "</pre>";
			$form->bind($submission);
			$form->get('submit')->setAttribute('value', 'Edit Lead');
			
			$request = $this->getRequest();
			if ($request->isPost()) {
				$form->setData($request->getPost());
				
				if ($form->isValid()) {
					$this->getSubmissionMapper()->saveSubmission($submission);
					
					$message = "Lead Entry saved.";
					$this->errorResponse->addMessage($message, "success");
					// Redirect to View Lead
					return $this->redirect()->toRoute('home', 
							array(
									'action' => 'view',
									'id' => $id
							), 
							array(
									'query' => array(
											'msg' => 1
									)
							));
				} else {
					$message = array(
							"You have invalid Form Entries."
					);
					$this->errorResponse->addMessages(null, $message, 
							$form->getMessages());
				}
			}
		} else {
			$message = "A Lead could not be found with the ID: \"{$id}\". Return to <a href=\"javascript:history.back()\" class=\"alert-link\">previous page</a>?";
			$this->errorResponse->addMessage($message, "error");
		}
		
		return array(
				'id' => $id,
				'form' => $form
		);
	}

	public function exportAction ()
	{
		$results = array();
		$leadForm = new LeadForm();
		$labels = array();
		$headings = array();
		
		$lead = new LeadEntity();
		$form = new FormEntity();
		$detail = new DetailEntity();
		
		$collection = array(
				'lead' => $lead,
				'form' => $form,
				'detail' => $detail
		);
		
		foreach (array_keys($collection) as $fieldsetName) {
			$fieldset = $leadForm->get($fieldsetName);
			foreach ($fieldset as $name => $element) {
				$labels["{$fieldsetName}[{$name}]"] = $element->getLabel();
			}
		}
		
		foreach ($collection as $name => $entity) {
			$data = $entity->getArrayCopy();
			unset($data['array_copy']);
			foreach (array_keys($data) as $heading) {
				$headings[] = isset($labels["{$name}[{$heading}]"]) ? $labels["{$name}[{$heading}]"] : "{$name}[{$heading}]";
			}
		}
		
		$submissions = $this->getSubmissionMapper()->fetchAll();
		
		return $this->csvExport('Lead Report (' . date('Y-m-d') . ').csv', 
				$headings, $submissions, 
				array(
						$this,
						'extractSubmission'
				));
	}

	public function extractSubmission (SubmissionEntity $submission)
	{
		return $this->getSubmissionMapper()->extract($submission);
	}

	public function submitAction ()
	{
		$request = $this->getRequest();
		$post = $request->getPost()->toArray();
		
		$response = null;
		
		$result = null;
		
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (! $id) {
			// New Submission ?
			$response = $this->addAction();
			
			if (isset($response['id'])) {
				$id = $response['id'];
			}
		}
		if ($id) {
			$result = $this->submit($id);
			
			if ($result && $result instanceof JsonModel) {
				$data = $result->getVariable("data");
				if ($data["submitted"] == 1) {
					// Redirect to View Lead
					$message = "Your Lead has been submitted.";
					$this->errorResponse->addMessage($message, "success");
				} else {
					$message = isset($data['lastresponse']) ? $data['lastresponse'] : 'An Unknown Error has occurred.';
					$this->errorResponse->addMessage($message, "error");
				}
			} else {
				$message = 'An Unknown Error has occurred.';
				$this->errorResponse->addMessage($message, "error");
			}
			return $this->redirect()->toRoute('home', 
					array(
							'action' => 'view',
							'id' => $id
					), 
					array(
							'query' => array(
									'msg' => 1
							)
					));
		}
		
		// Redirect to Home
		return $this->redirect()->toRoute('home', 
				array(
						'action' => 'index'
				));
	}

	public function batchsubmitAction ()
	{
		$request = $this->getRequest();
		$post = $request->getPost('sel');
		
		foreach (array_keys($post) as $id) {
			$this->submit($id);
		}
		
		// Redirect to Home
		$message = "Lead(s) Submitted.";
		$this->errorResponse->addMessage($message, "success");
		return $this->redirect()->toRoute('home', 
				array(
						'action' => 'index'
				), 
				array(
						'query' => array(
								'msg' => 1
						)
				));
	}

	protected function submit ($id)
	{
		// Do something with API Key ???
		return $this->forward()->dispatch('TenStreet\Controller\SoapClient', 
				array(
						'action' => 'send',
						'id' => $id
				));
	}

	protected function getLeadMapper ()
	{
		$sm = $this->getServiceLocator();
		return $sm->get('LeadMapper');
	}

	protected function getSubmissionMapper ()
	{
		$sm = $this->getServiceLocator();
		return $sm->get('SubmissionMapper');
	}
}
