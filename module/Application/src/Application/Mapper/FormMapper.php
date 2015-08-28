<?php
namespace Application\Mapper;
use Zend\Db\Adapter\Adapter;
use Application\Entity\Form as FormEntity;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\HydratingResultSet;

class FormMapper
{

	protected $tableName = 'form';

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
		$select->order(array(
				"id ASC"
		));
		$entityPrototype = new FormEntity();
		$hydrator = new ClassMethods();
		
		$resultset = new HydratingResultSet($hydrator, $entityPrototype);
		$statement = $this->sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		$resultset->initialize($results);
		return $resultset;
	}

	public function getForm ($id)
	{
		$select = $this->sql->select();
		$select->where(array(
				'id' => $id
		));
		
		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute()->current();
		if (! $result) {
			return null;
		}
		
		$hydrator = new ClassMethods();
		$form = new FormEntity();
		$hydrator->hydrate($result, $form);
		
		return $form;
	}
}
