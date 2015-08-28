<?php
namespace TenStreet\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceManager;

class AuthTable
{

	protected $dbAdapter;

	protected $tableGateway;

	public function __construct (TableGateway $tableGateway, ServiceManager $sm)
	{
		$this->tableGateway = $tableGateway;
		
		$this->dbAdapter = $this->tableGateway->getAdapter();
	}
	
	public function get()
	{
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
		return $resultSet->current();		
	}
}
