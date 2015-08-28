<?php
namespace TenStreet\Mapper;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Adapter\Adapter;
use TenStreet\Entity\TenStreetData;
use Application\Entity\Submission as SubmissionEntity;
use TenStreet\Hydrator\TenStreetHydrator;
use TenStreet\Entity\ApplicationData\DisplayFields\DisplayField;
use TenStreet\Entity\PersonalData\PersonalData;
use TenStreet\Entity\PersonalData\PersonName;
use TenStreet\Entity\PersonalData\PostalAddress;
use TenStreet\Entity\ApplicationData\ApplicationData;
use TenStreet\Entity\PersonalData\ContactData;

class TenStreetDataMapper
{

	protected $dbAdapter;

	protected $authTable;

	protected $submissionMapper;

	protected $sm;

	public function __construct (ServiceManager $sm, Adapter $dbAdapter)
	{
		$this->sm = $sm;
		
		$this->authTable = $this->sm->get('TenStreet\Model\AuthTable');
		
		$this->dbAdapter = $dbAdapter;
		
		$this->submissionMapper = $this->sm->get('SubmissionMapper');
	}

	public function getById ($id, $service = 'subject_upload')
	{
		$submission = $this->submissionMapper->getSubmission($id);
		
		$tenStreetData = $this->getTenStreetData($submission);
		
		$Authentication = $this->getAuthorization($service);
		
		$ApplicationData = $this->getApplicationData($submission);
		
		$PersonalData = $this->getPersonalData($submission);
		
		$displayFields = $this->getDisplayField($submission);
		
		$tenStreetData->setApplicationData($ApplicationData);
		
		$tenStreetData->setAuthentication($Authentication);
		
		$tenStreetData->setPersonalData($PersonalData);
		
		return $tenStreetData;
	}

	private function extract ($object)
	{
		$hydrator = new TenStreetHydrator(false);
		
		return $hydrator->extract($object);
	}

	public function getCredentials ($service = 'subject_upload')
	{
		$authEntity = $this->authTable->get();
		
		$authEntity->setService($service);
		
		return $authEntity;
	}

	protected function getAuthorization ($service)
	{
		$authEntity = $this->authTable->get();
		
		$authEntity->setService($service);
		
		return $authEntity;
	}

	protected function getTenStreetData (SubmissionEntity $submission)
	{
		$gConfig = $this->sm->get('Config');
		
		$config = $gConfig['TenStreet'];
		
		$tenStreetEntity = new TenStreetData();
		
		$Mode = getenv("APPLICATION_ENV") == 'development' ? 'DEV' : 'PROD';
		
		$Source = $config['Entity']['TenStreetData']['Source'];
		
		$CompanyId = ($Mode == 'DEV') ? $config['Entity']['TenStreetData']['CompanyId'] : $submission->getLead()->getCompanyid();
		
		$tenStreetEntity->setMode($Mode);
		
		$tenStreetEntity->setCompanyId($CompanyId);
		
		$tenStreetEntity->setSource($Source);
		
		return $tenStreetEntity;
	}

	protected function getDisplayField (SubmissionEntity $submission)
	{
		$detail = $submission->getDetail();
		
		$data = $this->extract($detail);
		
		$displayFields = array();
		
		if ($data) {
			for ($i = 1; $i < 4; $i ++) {
				if (isset($data["Question{$i}"])) {
					$displayField = new DisplayField();
					$DisplayPrompt = $data["Question{$i}"];
					$DisplayValue = isset($data["Answer{$i}"]) ? $data["Answer{$i}"] : "";
					$displayField->setDisplayPrompt($DisplayPrompt);
					$displayField->setDisplayValue($DisplayValue);
					$displayFields[] = $displayField;
				}
			}
		}
		return $displayFields;
	}

	protected function getApplicationData (SubmissionEntity $submission)
	{
		$applicationData = new ApplicationData();
		
		$AppReferrer = $submission->getLead()->getReferrer();
		
		$DisplayField = $this->getDisplayField($submission);
		
		$applicationData->setAppReferrer($AppReferrer);
		
		$applicationData->setDisplayField($DisplayField);
		
		return $applicationData;
	}

	protected function getPersonName (SubmissionEntity $submission)
	{
		$personName = new PersonName();
		
		$detail = $submission->getDetail();
		
		$FamilyName = $detail->getLastName();
		
		$GivenName = $detail->getFirstName();
		
		$personName->setFamilyName($FamilyName);
		
		$personName->setGivenName($GivenName);
		
		return $personName;
	}

	protected function getPostalAddress (SubmissionEntity $submission)
	{
		$postalAddress = new PostalAddress();
		
		$detail = $submission->getDetail();
		
		$Municipality = $detail->getCity();
		
		$Region = $detail->getState();
		
		$postalAddress->setMunicipality($Municipality);
		
		$postalAddress->setRegion($Region);
		
		return $postalAddress;
	}

	protected function getContactData (SubmissionEntity $submission)
	{
		$contactData = new ContactData();
		
		/*
		 * $attributes = array(
		 * "PreferredMethod" => "PrimaryPhone",
		 * "PreferredTime" => "Any"
		 * );
		 */
		
		$InternetEmailAddress = $submission->getDetail()->getEmail();
		
		$PrimaryPhone = $submission->getDetail()->getPhone();
		
		// $contactData->setAttributes($attributes);
		
		$contactData->setInternetEmailAddress($InternetEmailAddress);
		
		$contactData->setPrimaryPhone($PrimaryPhone);
		
		return $contactData;
	}

	protected function getPersonalData (SubmissionEntity $submission)
	{
		$personalData = new PersonalData();
		
		$PersonName = $this->getPersonName($submission);
		
		$PostalAddress = $this->getPostalAddress($submission);
		
		$ContactData = $this->getContactData($submission);
		
		$personalData->setPersonName($PersonName);
		
		$personalData->setPostalAddress($PostalAddress);
		
		$personalData->setContactData($ContactData);
		
		return $personalData;
	}
}