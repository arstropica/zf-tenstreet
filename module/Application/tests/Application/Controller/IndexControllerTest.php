<?php
require_once 'module/Application/src/Application/Controller/IndexController.php';
use Application\Controller\IndexController;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * IndexController test case.
 */
class IndexControllerTest extends AbstractHttpControllerTestCase
{

	/**
	 *
	 * @var IndexController
	 */
	private $indexController;

	protected $traceError = true;

	/**
	 *
	 * @var DocRoot
	 */
	private $docroot = '/home/apiuser/public_html';

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		$this->setApplicationConfig(include 'config/application.config.php');
		parent::setUp();
		$this->mockBjyAuthorize();
		
		// TODO Auto-generated IndexControllerTest::setUp()
		
		$this->indexController = new IndexController();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated IndexControllerTest::tearDown()
		$this->indexController = null;
		
		parent::tearDown();
	}

	/* Bypass BjyAuthorize Authentication */
	protected function mockBjyAuthorize ()
	{
		// Creating mock
		$mockBjy = $this->getMock('BjyAuthorize\Service\Authorize', 
				array(
						"isAllowed"
				), 
				array(
						$this->getApplicationConfig(),
						$this->getApplication()
							->getServiceManager()
				));
		
		// Bypass auth, force true
		$mockBjy->expects($this->any())
			->method('isAllowed')
			->will($this->returnValue(true));
		
		// Overriding BjyAuthorize\Service\Authorize service
		$this->getApplication()
			->getServiceManager()
			->setAllowOverride(true)
			->setService('BjyAuthorize\Service\Authorize', $mockBjy);
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		// TODO Auto-generated constructor
	}

	/**
	 * Tests IndexController->__construct()
	 */
	public function test__construct ()
	{
		$this->indexController->__construct();
		
		$this->assertClassHasAttribute('errorResponse', 
				'Application\Controller\IndexController');
	}

	/*
	 * Dispatches the /album URL, asserts that the response code is 200, and
	 * that we ended up in the desired module and controller.
	 */
	public function testIndexActionCanBeAccessed ()
	{
		$this->dispatch('/');
		$this->assertResponseStatusCode(200);
		
		$this->assertModuleName('Application');
		$this->assertControllerName('Application\Controller\Index');
		$this->assertControllerClass('IndexController');
		$this->assertMatchedRouteName('home');
	}

	/**
	 * Tests IndexController->viewAction()
	 */
	public function testViewAction ()
	{
		// TODO Auto-generated IndexControllerTest->testViewAction()
		$this->markTestIncomplete("viewAction test not implemented");
		
		$this->indexController->viewAction(/* parameters */);
	}

	/**
	 * Tests IndexController->addAction()
	 */
	public function testAddAction ()
	{
		$submissionMapperMock = $this->getMockSubmissionMapper();
		
		$submissionMapperMock->expects($this->once())
			->method('saveSubmission')
			->will($this->returnValue(null));
		
		$serviceManager = $this->getApplicationServiceLocator();
		$serviceManager->setAllowOverride(true);
		$serviceManager->setService('SubmissionMapper', $submissionMapperMock);
		
		$postData = $this->getSamplePostData();
		
		$this->dispatch('/add', 'POST', $postData);
		$this->assertResponseStatusCode(302);
		
		$this->assertRedirectTo('/?msg=1');
	}

	/**
	 * Tests IndexController->editAction()
	 */
	public function testEditAction ()
	{
		$submissionMapperMock = $this->getMockSubmissionMapper();
		
		$submissionMapperMock->expects($this->once())
			->method('saveSubmission')
			->will($this->returnValue(null));
		
		/*
		 * $submissionMapperMock->expects($this->once())
		 * ->method('getSubmission')
		 * ->will($this->returnValue(null));
		 */
		
		$serviceManager = $this->getApplicationServiceLocator();
		$serviceManager->setAllowOverride(true);
		$serviceManager->setService('SubmissionMapper', $submissionMapperMock);
		
		$postData = $this->getSamplePostData();
		
		$postData["id"] = 3;
		$postData['lead']['id'] = 3;
		$postData['lead']['formid'] = 1;
		$postData['lead']['driverid'] = "";
		$postData['lead']['lastresponse'] = "";
		$postData['form'] = array(
				"id" => "1",
				"source" => "http://cnn.com",
				"form" => "Test Form",
				"companyid" => "15",
				"company" => "Test Company"
		);
		
		// $this->dispatch('/edit/3');
		$this->dispatch('/edit/3', 'POST', $postData);
		$this->assertResponseStatusCode(302);
		
		$this->assertRedirectTo('/view/3?msg=1');
	}

	/**
	 * Tests IndexController->exportAction()
	 */
	public function testExportAction ()
	{
		// TODO Auto-generated IndexControllerTest->testExportAction()
		$this->markTestIncomplete("exportAction test not implemented");
		
		$this->indexController->exportAction(/* parameters */);
	}

	/**
	 * Tests IndexController->extractSubmission()
	 */
	public function testExtractSubmission ()
	{
		// TODO Auto-generated IndexControllerTest->testExtractSubmission()
		$this->markTestIncomplete("extractSubmission test not implemented");
		
		$this->indexController->extractSubmission(/* parameters */);
	}

	/**
	 * Tests IndexController->submitAction()
	 */
	public function testSubmitAction ()
	{
		// TODO Auto-generated IndexControllerTest->testSubmitAction()
		$this->markTestIncomplete("submitAction test not implemented");
		
		$this->indexController->submitAction(/* parameters */);
	}

	/**
	 * Tests IndexController->batchsubmitAction()
	 */
	public function testBatchsubmitAction ()
	{
		// TODO Auto-generated IndexControllerTest->testBatchsubmitAction()
		$this->markTestIncomplete("batchsubmitAction test not implemented");
		
		$this->indexController->batchsubmitAction(/* parameters */);
	}

	/**
	 * Mocks IndexController->getLeadMapper()
	 */
	public function getMockLeadMapper ()
	{
		return $this->getMockBuilder('Application\Mapper\LeadMapper')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Mocks IndexController->getSubmissionMapper()
	 */
	public function getMockSubmissionMapper ()
	{
		return $this->getMockBuilder('Application\Mapper\SubmissionMapper')
			->disableOriginalConstructor()
			->getMock();
	}

	public function getSamplePostData ()
	{
		return array(
				"lead" => [
						// "id" => "",
						// "formid" => "",
						"companyid" => "15",
						"company" => "Test Company",
						"ipaddress" => "127.0.0.1",
						"referrer" => "http://cnn.com/index.php",
						"formname" => "Test Form",
						"timecreated" => "2015-07-29T10:00",
						"timesubmitted" => "",
						"submitted" => "0"
				],
				// "driverid" => "",
				// "lastresponse" => ""
				"detail" => [
						"FirstName" => "Joe",
						"LastName" => "Bloggs",
						"City" => "Nowhere",
						"State" => "GA",
						"Email" => "joe@plumber.com",
						"Phone" => "6789999999",
						"Question1" => "Knock",
						"Answer1" => "Knock",
						"Question2" => "Who's",
						"Answer2" => "There",
						"Question3" => "Knuck...",
						"Answer3" => "Cles..."
				],
				"form" => [
						"id" => "",
						"source" => "",
						"form" => "",
						"companyid" => "",
						"company" => ""
				]
		);
	}
}

