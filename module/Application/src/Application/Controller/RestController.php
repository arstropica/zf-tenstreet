<?php
namespace Application\Controller;
use Zend\Mvc\Controller\AbstractRestfulController;
use Application\Form\LeadForm;
use Zend\View\Model\JsonModel;
use Application\Entity\Submission as SubmissionEntity;
use Zend\EventManager\EventManagerInterface;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use Application\Controller\Plugin\JSONErrorResponse as ErrorResponse;

class RestController extends AbstractRestfulController
{

	protected $collectionMethods = array(
			'GET',
			'POST'
	);

	protected $resourceMethods = array(
			'GET',
			'PUT',
			'DELETE'
	);

	protected $server;

	protected $errorResponse;

	public function __construct (OAuth2Server $server)
	{
		$this->errorResponse = new ErrorResponse($this);
		$this->server = $server;
	}

	protected function authorize ()
	{
		if (! $this->server->verifyResourceRequest(
				OAuth2Request::createFromGlobals())) {
			// Not authorized return 401 error
			return false;
		}
		return true;
	}

	public function indexAction ()
	{
		if (! $this->authorize()) {
			return $this->errorResponse->errorHandler(401, "Not Authorized.");
		}
		$results = $this->getList();
		$response = $this->getResponse();
		$response->setStatusCode(200);
		return new JsonModel($results);
	}

	public function addAction ()
	{
		if (! $this->authorize()) {
			return $this->errorResponse->errorHandler(401, "Not Authorized.");
		}
		$request = $this->getRequest();
		$result = array(
				'data' => null
		);
		if ($request->isPost()) {
			$data = $request->getPost();
			$result = $this->create($data);
		}
		
		$response = $this->getResponse();
		$response->setStatusCode(201);
		return new JsonModel($result);
	}

	public function submitAction ()
	{
		if (! $this->authorize()) {
			return $this->errorResponse->errorHandler(401, "Not Authorized.");
		}
		$request = $this->getRequest();
		$result = array(
				'data' => null
		);
		if ($request->isPost()) {
			$data = $request->getPost();
			$result = $this->create($data);
			
			if ($result && isset($result['data']['id'])) {
				$id = $result['data']['id'];
				return $this->submit($id);
			}
		}
		
		return $this->errorResponse->errorHandler(400, "Operation Failed.", 
				$result);
	}

	public function editAction ()
	{
		if (! $this->authorize()) {
			return $this->errorResponse->errorHandler(401, "Not Authorized.");
		}
		$request = $this->getRequest();
		$result = array(
				'data' => null
		);
		if ($request->isPost()) {
			$data = $request->getPost();
			$id = $request->getPost('id');
			$result = $this->update($id, $data);
		}
		
		$response = $this->getResponse();
		$response->setStatusCode(200);
		return new JsonModel($result);
	}

	public function viewAction ()
	{
		if (! $this->authorize()) {
			return $this->errorResponse->errorHandler(401, "Not Authorized.");
		}
		$id = (int) $this->params()->fromRoute('id', 0);
		if (! $id) {
			return $this->redirect()->toRoute('rest-api', 
					array(
							'action' => 'index'
					), array(), true);
		}
		$result = array(
				'data' => null
		);
		$submission = $this->getSubmissionMapper()->getSubmission($id);
		$data = array();
		$data['lead'] = $submission->getLead()->getArrayCopy();
		$data['form'] = $submission->getForm()->getArrayCopy();
		$data['detail'] = $submission->getDetail()->getArrayCopy();
		$result['data'] = $data;
		
		$response = $this->getResponse();
		$response->setStatusCode(200);
		return new JsonModel($result);
	}

	public function testAction ()
	{
		if (! $this->authorize()) {
			return $this->errorResponse->errorHandler(401, "Not Authorized.");
		}
		$response = array(
				'data' => array(
						'test' => 'success'
				)
		);
		return new JsonModel($response);
	}

	public function errorAction ()
	{
		return $this->errorResponse->errorHandler(400, "Unspecified Error.");
	}

	public function getSubmissionMapper ()
	{
		$sm = $this->getServiceLocator();
		return $sm->get('SubmissionMapper');
	}

	protected function allowMethods ()
	{
		if ($this->params()->fromRoute('id', false)) {
			// we have an ID, return specific item
			return $this->resourceMethods;
		}
		// no ID, return collection
		return $this->collectionMethods;
	}

	public function setEventManager (EventManagerInterface $events)
	{
		// events property defined in AbstractController
		$this->events = $events;
		parent::setEventManager($events);
		// Register the listener and callback method with a priority of 10
		$events->attach('dispatch', array(
				$this,
				'checkOptions'
		), 10);
	}

	public function checkOptions ($e)
	{
		if (in_array($e->getRequest()->getMethod(), $this->allowMethods())) {
			// Method Allowed, Nothing to Do
			return;
		} else {
			// Method Not Allowed
			return $this->errorResponse->methodNotAllowed();
		}
	}

	protected function submit ($id)
	{
		$access_token = $this->getRequest()->getPost('access_token');
		$response = false;
		try {
			$response = $this->forward()->dispatch(
					'TenStreet\Controller\SoapClient', 
					array(
							'action' => 'send',
							'id' => $id
					), 
					array(
							"query" => array(
									"access_token" => $access_token
							)
					), true);
		} catch (\Exception $e) {
			return $this->errorResponse->errorHandler(400, $e->getMessage(), 
					$e->getTrace());
		}
		
		return $response;
	}
	
	// Class Methods
	public function create ($data)
	{
		$id = null;
		$result = null;
		
		$form = new LeadForm();
		$submission = new SubmissionEntity();
		
		$form->bind($submission);
		
		$form->setData($data);
		
		if ($form->isValid()) {
			$id = $this->getSubmissionMapper()->saveSubmission($submission);
		} else {
			return $this->errorResponse->errorHandler(400, 
					"Invalid Submission.", $form->getMessages());
		}
		if ($id) {
			$result = $this->get($id);
		}
		
		return $result;
	}

	public function getList ()
	{
		$results = array();
		$submissions = $this->getSubmissionMapper()->fetchAll();
		
		foreach ($submissions as $submission) {
			$result = array();
			$result['lead'] = $submission->getLead()->getArrayCopy();
			$result['form'] = $submission->getForm()->getArrayCopy();
			$result['detail'] = $submission->getDetail()->getArrayCopy();
			$results[] = $result;
		}
		return $results;
	}

	public function update ($id, $data)
	{
		$result = null;
		$data['id'] = $id;
		$submission = $this->getSubmissionMapper()->getSubmission($id);
		$form = new LeadForm();
		$form->bind($submission);
		$form->setData($data);
		if ($form->isValid()) {
			$id = $this->getSubmissionMapper()->saveSubmission($submission);
		} else {
			return $this->errorResponse->errorHandler(400, 
					"Invalid Submission.");
		}
		
		if ($id) {
			$result = $this->get($id);
		}
		
		return $result;
	}

	public function options ()
	{
		$response = $this->getResponse();
		// If in Options Array, Allow
		$response->getHeaders()->addHeaderLine('Allow', 
				implode(',', $this->allowMethods()));
		// Return Response
		return $response;
	}

	public function get ($id)
	{
		$submission = $this->getSubmissionMapper()->getSubmission($id);
		
		return array(
				"data" => $submission->getArrayCopy()
		);
	}
	
	// Override default actions as they do not return valid JsonModels
	public function delete ($id)
	{
		return $this->errorResponse->methodNotAllowed();
	}

	public function deleteList ($data)
	{
		return $this->errorResponse->methodNotAllowed();
	}

	public function head ($id = null)
	{
		return $this->errorResponse->methodNotAllowed();
	}

	public function patch ($id, $data)
	{
		return $this->errorResponse->methodNotAllowed();
	}

	public function replaceList ($data)
	{
		return $this->errorResponse->methodNotAllowed();
	}

	public function patchList ($data)
	{
		return $this->errorResponse->methodNotAllowed();
	}
}
