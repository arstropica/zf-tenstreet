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
use Application\Form\LeadImportForm;
use Zend\View\Model\JsonModel;
use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size;
use Zend\Validator\File\Extension as FileExt;

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
		$sl = $this->getServiceLocator();
		$request = $this->getRequest();
		$filters = $sl->get('FormElementManager')->get(
				'\Application\Form\LeadFilterForm');
		
		$results = array();
		$leadForm = new LeadForm();
		$labels = array();
		$headings = array();
		
		$lead = new LeadEntity();
		$form = new FormEntity();
		$detail = new DetailEntity();
		
		$data = $request->getQuery();
		$fdata = array();
		$order = 'desc';
		$sort = 'lead.id';
		
		if ($data) {
			$filters->setData($data);
			if ($filters->isValid()) {
				$fdata = $filters->getData();
			}
		}
		
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
		
		// grab the paginator from the LeadTable
		$submissions = $this->getSubmissionMapper()->fetchAll(false, $sort, 
				$order, $fdata);
		
		return $this->csvExport('Lead Report (' . date('Y-m-d') . ').csv', 
				$headings, $submissions, 
				array(
						$this,
						'extractSubmission'
				));
	}

	public function importAction ()
	{
		$sl = $this->getServiceLocator();
		$view = $sl->get('viewhelpermanager');
		$view->get('HeadScript')->appendFile(
				$view->get('basePath')
					->__invoke('js/app.js'));
		
		$view->get('HeadLink')->appendStylesheet(
				$view->get('basePath')
					->__invoke('css/nav-wizard.bootstrap.css'));
		
		$request = $this->getRequest();
		
		$results = array(
				'fields' => false,
				'stage' => 1,
				'headings' => [],
				'form' => false,
				'_tmp' => false,
				'dataCount' => false,
				'data' => false,
				'valid' => false
		);
		$form = new LeadImportForm();
		
		$post = $request->getPost()->toArray();
		$files = $request->getFiles()->toArray();
		
		if ($post || $files) {
			$results['stage'] = 2;
			// set data post and file ...
			$data = array_merge_recursive($post, 
					$request->getFiles()->toArray());
			
			$form->setData($data);
			
			if ($form->isValid()) {
				// Handle Uploads
				if ($files) {
					$file = $this->params()->fromFiles('leadsUpload');
					$tmp_file = $this->validateImportFile($file);
					if ($tmp_file) {
						$csv = $this->extractCSV(
								$this->getUploadPath() . '/' . $tmp_file);
						
						if ($csv['count']) {
							// Setup Import Form
							$fieldSet = $form->addImportFieldset(
									array_combine($csv['headings'], 
											$csv['headings']));
							$form->get('leadTmpFile')->setValue($tmp_file);
							$form->get('submit')->setValue('Import');
							
							$results['_tmp'] = $tmp_file;
							$results['count'] = $csv['count'];
							$results['headings'] = $csv['headings'];
							$results['fields'] = $fieldSet::getStructuredFieldNames();
						}
					}
				}
				
				// Handle Field Matching
				if ($post && isset($post['match'], $post['leadTmpFile'])) {
					$importFieldset = $form->getImportFieldset();
					$results['stage'] = 3;
					$match = $post['match'];
					$tmp_file = $this->getUploadPath() . '/' .
							 $post['leadTmpFile'];
					$csv = $this->extractCSV($tmp_file);
					if ($csv['count']) {
						$results['data'] = $this->mapImportedValues(
								$csv['body'], $match, false);
						if ($results['data']) {
							$results['valid'] = array_map(
									function  ($v)
									{
										return $v ? 'valid' : 'invalid';
									}, 
									array_map(
											array(
													$this,
													'checkDuplicateImport'
											), $results['data']));
							if (($invalid = array_keys($results['valid'], 
									'invalid')) === true) {
								$this->errorResponse->addMessage(
										count($invalid) .
												 " duplicate leads were found in your imported data.", 
												"error");
							}
							$form = $this->addImportFields($form, 
									$results['data'], $results['valid']);
						}
						$results['headings'] = array_intersect(
								$importFieldset::getFieldNames(), 
								[
										'timecreated',
										'FirstName',
										'LastName',
										'Email'
								]);
						$form->addConfirmField();
						$form->get('submit')->setValue('Confirm');
					} else {
						$message = "No valid records could be imported.";
						$this->errorResponse->addMessage($message, "error");
					}
				}
				// Handle Import save
				if ($post && isset($post['confirm'], $post['submissions'])) {
					$importOutcome = false;
					$submissions = $post['submissions'];
					if ($submissions && is_array($submissions)) {
						$importOutcome = true;
						foreach ($submissions as $extract) {
							$submission = $this->getSubmissionMapper()->hydrate(
									$extract, 'flat');
							if ($submission) {
								$this->getSubmissionMapper()->saveSubmission(
										$submission);
							} elseif ($importOutcome) {
								$importOutcome = false;
							}
						}
					}
					if (! $importOutcome) {
						$message = "One or more records could not be imported.";
						$this->errorResponse->addMessage($message, "error");
					} else {
						$message = count($submissions) .
								 " records were imported.";
						$this->errorResponse->addMessage($message, "success");
					}
					return $this->redirect()->toRoute('home', 
							array(
									'action' => $importOutcome ? 'index' : 'import'
							), 
							array(
									'query' => array(
											'msg' => 1
									)
							));
				}
			} else {
				$message = array(
						"You have invalid Form Entries."
				);
				$this->errorResponse->addMessages(null, $message, 
						$form->getMessages());
			}
		} else {
			$form->addUploadField();
		}
		
		$results['form'] = $form;
		return $results;
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
				if (is_array($data) && isset($data['submitted']) &&
						 $data["submitted"] == 1) {
					// Redirect to View Lead
					$message = "Your Lead has been submitted.";
					$this->errorResponse->addMessage($message, "success");
				} elseif ($data instanceof JsonModel) {
					$err = $data->getVariables();
					if (is_string($err['error'])) {
						$message = $err['error'];
					} else {
						$message = 'An Unknown Error has occurred.';
					}
					$this->errorResponse->addMessage($message, "error");
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

	protected function extractSubmission (SubmissionEntity $submission)
	{
		return $this->getSubmissionMapper()->extract($submission);
	}

	protected function getSubmissionFields (SubmissionEntity $submission)
	{
		return $this->getSubmissionMapper()->getFields($submission, false);
	}

	protected function validateImportFile ($file)
	{
		$size = new Size(
				array(
						'min' => 1024,
						'max' => 2048000
				)); // min/max bytes filesize
		
		$adapter = new FileHttp();
		$ext = new FileExt(
				array(
						'extension' => array(
								'csv'
						)
				));
		$adapter->setValidators(array(
				$size,
				$ext
		), $file['name']);
		$isValid = $adapter->isValid();
		if (! $isValid) {
			$dataError = $adapter->getMessages();
			$this->errorResponse->addMessage($dataError, "error");
		} else {
			$adapter->setDestination($this->getUploadPath());
			if ($adapter->receive($file['name'])) {
				$isValid = $file['name'];
			}
		}
		return $isValid;
	}

	protected function getUploadPath ()
	{
		$config = $this->getServiceLocator()->get('config');
		return $config['upload_location'];
	}

	protected function extractCSV ($filepath)
	{
		$result = [
				'body' => false,
				'headings' => false,
				'count' => false
		];
		$csv = $this->csvImport($filepath);
		if ($csv && $csv instanceof \Iterator) {
			$result['body'] = [];
			foreach ($csv as $_csv) {
				$_row = [];
				foreach ($_csv as $k => $v) {
					$_row[$this->clean_bom($k)] = $v;
				}
				$result['body'][] = $_row;
			}
			$result['headings'] = array_keys(current($result['body']));
			$result['count'] = count($result['body']);
		}
		return $result;
	}

	protected function addImportFields (LeadImportForm $form, $data, 
			$valid = false)
	{
		if ($data) {
			foreach ($data as $i => $row) {
				if (! $valid || (isset($valid[$i]) && $valid[$i] == 'valid')) {
					foreach ($row as $field => $value) {
						$form->add(
								array(
										'name' => "submissions[{$i}][{$field}]",
										'attributes' => array(
												'value' => $value,
												'type' => 'hidden'
										)
								));
					}
				}
			}
		}
		return $form;
	}

	protected function mapImportedValues ($csv, $match, $structured = true)
	{
		$submissions = array();
		$i = 0;
		foreach ($csv as $row) {
			$mappedArray = $this->mapCSVRow($row, $match);
			if ($structured) {
				$submissions[$i] = $this->getSubmissionMapper()->extract(
						$this->getSubmissionMapper()
							->hydrate($mappedArray));
			} else {
				$submissions[$i] = $mappedArray;
			}
			$i ++;
		}
		
		return $submissions;
	}

	protected function checkDuplicateImport ($row)
	{
		$where = [
				'ipaddress' => $row['ipaddress'],
				'timecreated' => $row['timecreated']
		];
		
		return ! $this->getLeadMapper()->findLead($where);
	}

	protected function mapCSVRow ($row, $match)
	{
		$result = array();
		foreach ($match as $fieldName => $value) {
			if ($value && isset($row[$value])) {
				switch ($fieldName) {
					case 'Question1':
					case 'Question2':
					case 'Question3':
						$result[$fieldName] = $value;
						$result['Answer' . substr($fieldName, - 1)] = $row[$value];
						break;
					default:
						$result[$fieldName] = $row[$value];
						break;
				}
			}
		}
		return $result;
	}

	protected function clean_bom ($str)
	{
		return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), '"');
	}
}
