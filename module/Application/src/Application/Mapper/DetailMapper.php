<?php
namespace Application\Mapper;
use Zend\Db\Adapter\Adapter;
use Application\Entity\Detail as DetailEntity;
// use Application\Entity\Attribute as AttributeEntity;
use Application\Mapper\AttributeMapper;
use Zend\Db\Sql\Sql;
use Application\Hydrator\DetailHydrator;

class DetailMapper
{

	protected $tableName = 'lead_attribute_values';

	protected $dbAdapter;

	protected $sql;

	protected $mapper;

	public function __construct (Adapter $dbAdapter)
	{
		$this->dbAdapter = $dbAdapter;
		$this->sql = new Sql($dbAdapter);
		$this->sql->setTable($this->tableName);
		$this->mapper = new AttributeMapper($dbAdapter);
	}

	public function getDetail ($id)
	{
		$attributes = $this->mapper->getAttributes($id);
		
		if (! $attributes) {
			return null;
		}
		
		$hydrator = new DetailHydrator();
		$detail = new DetailEntity();
		$hydrator->hydrate($attributes->toArray(), $detail);
		
		return $detail;
	}
}
