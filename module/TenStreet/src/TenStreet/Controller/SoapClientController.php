<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/TenStreet for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace TenStreet\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Soap\Client;
use Zend\View\Model\JsonModel;
use TenStreet\Controller\Plugin\ErrorResponse;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use Zend\ServiceManager\ServiceLocatorInterface;

class SoapClientController extends AbstractActionController
{

	protected $wsdl;

	protected $env;

	protected $client;

	protected $rootNode;

	protected $errorResponse;

	protected $server;

	protected $logger;

	public function __construct (OAuth2Server $server, 
			ServiceLocatorInterface $sm)
	{
		$this->env = getenv("APPLICATION_ENV") ?  : 'development';
		
		$this->errorResponse = new ErrorResponse($this);
		
		$this->server = $server;
		
		$gConfig = $sm->get('Config');
		
		$config = $gConfig['TenStreet'];
		
		$this->rootNode = $config['Controller']['SoapClient']['rootNode'];
		
		$this->wsdl = $config['Controller']['SoapClient']['wsdl'];
		
		$this->logger = $sm->get('Logger');
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
		if (! $this->authorize() && ! $this->isUserAuthorized()) {
			return $this->errorResponse->insufficientAuthorization();
		}
		return $this->errorResponse->methodNotAllowed();
	}

	public function sendAction ()
	{
		if (! $this->authorize() && ! $this->isUserAuthorized()) {
			return $this->errorResponse->insufficientAuthorization();
		}
		
		$result = array(
				"data" => null
		);
		
		$_response = null;
		
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (! $id) {
			return $this->errorResponse->missingParameter();
		}
		
		$xml_data = $this->getData($id);
		
		$credentials = $this->getTenStreetDataMapper()->getCredentials();
		
		$clientId = $credentials->getClientId();
		
		$password = $credentials->getPassword();
		
		$service = $credentials->getService();
		
		if (isset($clientId, $password, $service)) {
			try {
				$_response = $this->PostClientData($id, $xml_data, $clientId, 
						$password, $service);
			} catch (\Exception $e) {
				return $this->errorResponse->errorHandler(400, $e->getMessage());
			}
		} else {
			return $this->errorResponse->insufficientAuthorization();
		}
		
		$result["data"] = $_response;
		
		$response = $this->getResponse();
		$response->setStatusCode(201);
		
		return new JsonModel($result);
	}

	public function preDispatch ()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}

	protected function getClient ()
	{
		if (! $this->client) {
			$clientOptions = array(
					'compression' => SOAP_COMPRESSION_ACCEPT,
					'soap_version' => SOAP_1_1,
					'connection_timeout' => 5
			);
			
			$this->client = new Client($this->wsdl[$this->env], $clientOptions);
		}
		
		return $this->client;
	}

	protected function getData ($id)
	{
		$TenStreetData = $this->getTenStreetDataMapper()->getById($id);
		
		try {
			$data = $this->getServiceLocator()
				->get('HydratorManager')
				->get('TenStreetDataHydrator')
				->extract($TenStreetData);
		} catch (\Exception $e) {
			throw new \Exception("Could not retrieve data for Lead {$id}.");
			return;
		}
		
		if ($data) {
			return $this->array2xml($this->rootNode, $data, false);
		} else {
			throw new \Exception("Could not retrieve data for Lead {$id}.");
		}
		return null;
	}

	protected function parseResponse ($response)
	{
		$result = array(
				'submitted' => 0
		);
		
		if (isset($response['TenstreetResponse'])) {
			$r = $response['TenstreetResponse'];
			$result['lastresponse'] = $r['Description'];
			if (isset($r['Status'])) {
				$result['submitted'] = $r['Status'] == 'Accepted' ? 1 : 0;
				if ($result['submitted']) {
					$result['timesubmitted'] = date('Y-m-d H:i:s', 
							strtotime($r['DateTime']));
					$result['driverid'] = $r['DriverId'];
				}
			}
		}
		
		return $result;
	}

	protected function saveResponse ($id, $response)
	{
		$result = $this->parseResponse($response);
		
		$submission = $this->getSubmissionMapper()->getSubmission($id);
		
		if ($submission && $submission instanceof \Application\Entity\Submission) {
			
			$lead = $submission->getLead();
			if ($lead && $lead instanceof \Application\Entity\Lead) {
				foreach ($result as $name => $value) {
					switch ($name) {
						case 'submitted':
							$lead->setSubmitted($value);
							break;
						case 'timesubmitted':
							$lead->setTimesubmitted($value);
							break;
						case 'driverid':
							$lead->setDriverid($value);
							break;
						case 'lastresponse':
							$lead->setLastresponse($value);
							break;
					}
				}
				$submission->setLead($lead);
				
				try {
					$this->getSubmissionMapper()->saveSubmission($submission);
				} catch (\Exception $e) {
					return $this->errorResponse->unknownError();
				}
			}
		}
		
		return $result;
	}

	protected function PostClientData ($id, $xml_data, $clientId, $password, 
			$service)
	{
		$xml_response = null;
		
		$hasException = false;
		$exception = false;
		try {
			$xml_response = $this->getClient()->PostClientData($xml_data, 
					$clientId, $password, $service);
		} catch (\SoapFault $e) {
			$hasException = true;
			$message = $this->client->getLastResponse();
			if (strlen($message)) {
				$exception = $this->xml2array($message);
				return $this->saveResponse($id, $exception);
			} else {
				return $this->errorResponse->errorHandler(400, $e->getMessage());
			}
		}
		$response = $this->xml2array($xml_response);
		
		return $this->saveResponse($id, $response);
	}

	protected function getTenStreetDataMapper ()
	{
		$sm = $this->getServiceLocator();
		return $sm->get('TenStreetDataMapper');
	}

	protected function xml2array ($xml)
	{
		return $this->easyXML()->xml2array($xml);
	}

	protected function getSubmissionMapper ()
	{
		$sm = $this->getServiceLocator();
		return $sm->get('SubmissionMapper');
	}

	protected function array2xml ($rootNode, $array, $encode = true)
	{
		$xml = $this->easyXML()->array2xml($rootNode, $array);
		$xmlObj = new \SimpleXMLElement($xml);
		$dom = dom_import_simplexml($xmlObj);
		$xml = $dom->ownerDocument->saveXML(
				$dom->ownerDocument->documentElement);
		return $encode ? "<![CDATA[" . $xml . "]]>" : $xml;
	}

	protected function isUserAuthorized ()
	{
		$role = false;
		if ($this->zfcUserAuthentication()
			->getAuthService()
			->hasIdentity()) {
			$roles = $this->serviceLocator->get(
					'BjyAuthorize\Provider\Identity\ProviderInterface')->getIdentityRoles();
			
			if ($roles && is_array($roles)) {
				$role = $roles[0]->getRoleId();
			}
			if (in_array($role, 
					[
							'administrator',
							'moderator',
							'user'
					])) {
				return true;
			} else {
				return false;
			}
		}
		return $role;
	}
}
