<?php
namespace Application\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Application\Entity\Lead as LeadEntity;
use Application\Entity\Submission as SubmissionEntity;
use Application\Entity\Attribute as AttributeEntity;
use Application\Entity\Form as FormEntity;
use Application\Hydrator\LeadHydrator;
use Application\Hydrator\FormHydrator;
use Application\Hydrator\DetailHydrator;
use Application\Hydrator\AttributeHydrator;
use Zend\Stdlib\Hydrator\ClassMethods;

class LeadTable
{

	protected $dbAdapter;

	protected $tableGateway;

	protected $tableGateways = array();

	protected $hydrators = array();

	public function __construct (TableGateway $leadTableGateway, 
			$dbAdapter = null)
	{
		$this->tableGateway = $leadTableGateway;
		
		$this->dbAdapter = $this->tableGateway->getAdapter();
		
		$this->tableGateways['lead'] = $this->tableGateway;
		$this->tableGateways['form'] = new TableGateway('form', $this->dbAdapter);
		$this->tableGateways['detail'] = new TableGateway('lead_attribute_values', 
				$this->dbAdapter);
		$this->tableGateways['attribute'] = new TableGateway('lead_attributes', 
				$this->dbAdapter);
		
		$this->hydrators['lead'] = new LeadHydrator();
		$this->hydrators['detail'] = new DetailHydrator();
		$this->hydrators['form'] = new FormHydrator();
		$this->hydrators['attribute'] = new AttributeHydrator();
	}

	public function fetchAll ($paging = false, $sort = "lead.id", $order = "ASC", 
			$filters = array())
	{
		$select = $this->tableGateway->getSql()->select();
		$select->join('form', 'form.id = lead.formid', 
				array(
						'source',
						'form.form' => 'form',
						'form.companyid' => 'companyid',
				), $select::JOIN_INNER);
		$select->order(array(
				"{$sort} {$order}"
		));
		$where = array();
		if ($filters) {
			foreach ($filters as $filter => $condition) {
				if (strlen($condition)) {
					switch ($filter) {
						case 'timecreated':
						case 'daterange':
							list ($from, $to) = array_map(
									function  ($d)
									{
										return date('Y-m-d', strtotime($d));
									}, explode("-", $condition));
							$where[] = "lead.timecreated between '{$from}' and '{$to}'";
							break;
						case 'status':
							$where[] = "lead.submitted = {$condition}";
							break;
						case 'sites':
							$where[] = "lead.formid = {$condition}";
							break;
						default:
							break;
					}
				}
			}
			if ($where) {
				$select->where($where);
			}
		}
		// echo "<pre>" . print_r($select->getSqlString(), true) . "</pre>";
		if ($paging) {
			// create a new result set based on the Album entity
			$resultSetPrototype = $this->tableGateway->getResultSetPrototype();
			$paginatorAdapter = new DbSelect(
					// our configured select object
					$select, 
					// the adapter to run it against
					$this->tableGateway->getAdapter(), 
					// the result set to hydrate
					$resultSetPrototype);
			$paginator = new Paginator($paginatorAdapter);
			return $paginator;
		}
		$resultSet = $this->tableGateway->selectWith($select);
		return $resultSet;
	}

	public function getLead ($id)
	{
		$id = (int) $id;
		
		$cache = null;
		
		$leadRowset = $this->tableGateway->select(
				array(
						'id' => array(
								$id
						)
				));
		$leadRowset->buffer();
		$leadRowData = $leadRowset->current()->getArrayCopy();
		
		if (! $leadRowData) {
			throw new \Exception("Could not find lead $id");
		} else {
			$leadRowData['detail'] = $this->getDetail($leadRowData['id']);
			$leadRowData['form'] = $this->getForm($leadRowData['formid']);
		}
		
		$lead = $this->hydrate($leadRowData, new LeadEntity());
		return $lead;
	}

	public function getForm ($id)
	{
		$resultSet = $this->tableGateways['form']->select(
				array(
						'id' => $id
				));
		$resultSet->buffer();
		$rowData = $resultSet->current()->getArrayCopy();
		
		if (! $rowData) {
			throw new \Exception("Could not find form $id");
		} else {
			return $rowData;
		}
		return false;
	}

	public function getDetail ($lead_id)
	{
		$detail = array();
		$select = $this->tableGateways['detail']->getSql()->select();
		$select->join('lead_attributes', 
				'lead_attributes.id = lead_attribute_values.attribute_id', 
				array(
						'attribute_name'
				));
		$select->where(array(
				'entity_id' => $lead_id
		));
		$resultSet = $this->tableGateways['detail']->selectWith($select);
		foreach ($resultSet as $row) {
			$rowData = $row->getArrayCopy();
			$detail[strtolower($rowData['attribute_name'])] = $rowData['value'];
		}
		
		if (! $detail) {
			throw new \Exception("Could not find Details for Lead $lead_id");
		} else {
			return $detail;
		}
		return false;
	}

	public function hydrate ($result, $entity)
	{
		$lead = $this->hydrators['lead']->hydrate((array) $result, $entity);
		
		return $lead;
	}

	public function saveLead (SubmissionEntity $submission)
	{
		if (! $submission->getLead()->getId()) {
			$result = $this->insert($submission);
		} else {
			$where = 'id = ' . (int) $submission->getLead()->getId();
			$result = $this->update($submission, $where);
		}
		return $result;
	}

	private function insert (SubmissionEntity $submission)
	{
		$lead = $submission->getLead();
		$formData = $this->hydrators['form']->extractFromSubmission($submission);
		$formEntity = $this->findForm($submission->getLead(), 'object');
		
		// Form Entity
		if ($formEntity) {
			// Form already exists for this lead
			// No need to create one... but set Form ID
			$formid = $formEntity->getId();
			$lead = $lead->setFormid($formid);
		} else {
			// Insert new Form
			$submission->setForm(
					$submission->getForm()
						->setId(null));
			$submission->setForm(
					$submission->getForm()
						->setForm($lead->getFormname()));
			$submission->setForm(
					$submission->getForm()
						->setCompanyid($lead->getCompanyid()));
			$submission->setForm(
					$submission->getForm()
					->setCompany($lead->getCompany()));
				
			$formData = $this->hydrators['form']->extractFromSubmission(
					$submission);
			$this->tableGateways['form']->insert($formData);
			// getting last inserted id
			$formid = $this->tableGateways['form']->lastinsertvalue;
			// set last inserted id as lead.formid
			$lead = $lead->setFormid($formid);
		}
		
		// Lead Entity
		$leadData = $this->hydrators['lead']->extract($lead);
		$this->tableGateways['lead']->insert($leadData);
		$leadid = $this->tableGateways['lead']->lastinsertvalue;
		
		// Detail Entity
		$attributes = array();
		$detailData = $this->hydrators['detail']->extract(
				$submission->getDetail());
		foreach ($detailData as $attribute => $value) {
			$attribute_id = $this->getAttributeId($attribute);
			if ($attribute_id) {
				$attributes[$attribute_id] = new AttributeEntity();
				$attributes[$attribute_id]->setAttributeid($attribute_id);
				$attributes[$attribute_id]->setEntityid($leadid);
				$attributes[$attribute_id]->setValue($value);
			} else {
				throw new \Exception("Could not find attribute $attribute");
				return false;
			}
		}
		
		foreach ($attributes as $attribute) {
			$attributeData = $this->hydrators['attribute']->extract($attribute);
			unset($attributeData['attribute_name']);
			$this->tableGateways['detail']->insert($attributeData);
		}
		return $leadid;
	}

	private function update (SubmissionEntity $submission, $where = null)
	{
		$lead = $submission->getLead();
		$leadid = $lead->getId();
		$formData = $this->hydrators['form']->extractFromSubmission($submission);
		$formEntity = $this->findForm($submission->getLead(), 'object');
		
		// Form Entity
		if ($formEntity) {
			// Form already exists for this lead
			// No need to create one... but set Form ID
			$formid = $formEntity->getId();
			$lead = $lead->setFormid($formid);
		} else {
			// Insert new Form
			$submission->setForm(
					$submission->getForm()
						->setId(null));
			$submission->setForm(
					$submission->getForm()
						->setForm($lead->getFormname()));
			$submission->setForm(
					$submission->getForm()
						->setCompanyid($lead->getCompanyid()));
			$submission->setForm(
					$submission->getForm()
					->setCompany($lead->getCompany()));
				
			$formData = $this->hydrators['form']->extractFromSubmission(
					$submission);
			$this->tableGateways['form']->insert($formData);
			// getting last inserted id
			$formid = $this->tableGateways['form']->lastinsertvalue;
			// set last inserted id as lead.formid
			$lead = $lead->setFormid($formid);
		}
		
		// Lead Entity
		$leadData = $this->hydrators['lead']->extract($lead);
		$this->tableGateways['lead']->update($leadData, $where);
		return $leadid;
	}

	public function findForm (LeadEntity $lead, $format = 'array')
	{
		$companyid = $lead->getCompanyid();
		$form = $lead->getFormname();
		$source = $this->hydrators['form']->getHost($lead->getReferrer());
		
		$formTableGateway = $this->tableGateways['form'];
		$resultSet = $formTableGateway->select(
				array(
						'companyid' => $companyid,
						'form' => $form,
						'source' => $source
				));
		
		$results = $resultSet->toArray();
		if ($results) {
			$hydrator = new ClassMethods();
			$form = new FormEntity();
			$hydrator->hydrate($results[0], $form);
			return $format == 'array' ? $form->getArrayCopy() : $form;
		}
		
		return false;
	}

	public function getSources ($active = true, $format = 'array')
	{
		$select = new Select();
		$select->from('form');
		$select->columns(
				array(
						'id' => 'form.id',
						'source' => 'form.source'
				), false);
		if ($active) {
			$select->join('lead', 'form.id = lead.formid', 
					array(
							'formid'
					), $select::JOIN_INNER);
		}
		$select->order(array(
				"form.source asc"
		));
		$select->group('form.id');
		// echo "<pre>" . print_r($select->getSqlString(), true) . "</pre>";
		$resultSet = $this->tableGateways['form']->selectWith($select);
		return $format == 'array' ? $resultSet->toArray() : $resultSet;
	}

	public function getAttributeId ($attribute)
	{
		$where = new Where();
		$where->expression('LOWER(attribute_name) LIKE ?', $attribute);
		$resultSet = $this->tableGateways['attribute']->select($where);
		if (! $resultSet || $resultSet->count() == 0) {
			throw new \Exception("Could not find attribute $attribute");
		} else {
			$row = $resultSet->current();
			return $row->id;
		}
		return false;
	}
}
