<?php
namespace Application\Mapper;
use Zend\Db\Adapter\Adapter;
use Application\Entity\Attribute as AttributeEntity;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\HydratingResultSet;

class AttributeMapper
{

	protected $tableName = 'lead_attribute_values';

	protected $dbAdapter;

	protected $sql;

	public function __construct (Adapter $dbAdapter)
	{
		$this->dbAdapter = $dbAdapter;
		$this->sql = new Sql($dbAdapter);
		$this->sql->setTable($this->tableName);
	}

	public function fetchAll ()
	{
		$select = $this->sql->select();
		$select->join('lead_attributes', 
				'lead_attribute_values.attribute_id = lead_attributes.id', 
				array(
						'attribute_name'
				));
		$select->order(array(
				"id ASC"
		));
		$entityPrototype = new AttributeEntity();
		$hydrator = new ClassMethods();
		
		$resultset = new HydratingResultSet($hydrator, $entityPrototype);
		$statement = $this->sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		$resultset->initialize($results);
		return $resultset;
	}

	public function getAttributes ($id)
	{
		$select = $this->sql->select();
		$select->join('lead_attributes', 
				'lead_attribute_values.attribute_id = lead_attributes.id', 
				array(
						'attribute_name'
				));
		$select->where(array(
				'entity_id' => $id
		));
		$select->order(array(
				"id ASC"
		));
		$entityPrototype = new AttributeEntity();
		$hydrator = new ClassMethods();
		
		$resultset = new HydratingResultSet($hydrator, $entityPrototype);
		$statement = $this->sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		$resultset->initialize($results);
		return $resultset;
	}
}
