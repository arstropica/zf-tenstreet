<?php
namespace Application\Mapper;
use Zend\Db\Adapter\Adapter;
use Application\Entity\Lead as LeadEntity;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\HydratingResultSet;
use Application\Hydrator\Strategy\DateTimeLocalStrategy;
use Application\Hydrator\Strategy\BooleanStrategy;

class LeadMapper
{

	protected $tableName = 'lead';

	protected $dbAdapter;

	protected $sql;

	public function __construct (Adapter $dbAdapter)
	{
		$this->dbAdapter = $dbAdapter;
		$this->sql = new Sql($dbAdapter);
		$this->sql->setTable($this->tableName);
	}

	public function fetchAll ($paging = false, $sort = "timecreated", $order = "DESC", 
			$filters = array())
	{
		$select = $this->sql->select();
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
									function  ($d, $i)
									{
										$t = $i == 'from' ? '00:00:00' : '23:59:59';
										return date('Y-m-d ' . $t, 
												strtotime($d));
									}, explode("-", $condition), [
											'from',
											'to'
									]);
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
		
		$entityPrototype = new LeadEntity();
		$hydrator = new ClassMethods();
		
		if ($paging) {
			// create a new result set based on the Lead entity
			$resultSet = new HydratingResultSet($hydrator, $entityPrototype);
			$paginatorAdapter = new DbSelect(
					// our configured select object
					$select, 
					// the adapter to run it against
					$this->dbAdapter, 
					// the result set to hydrate
					$resultSet);
			$paginator = new Paginator($paginatorAdapter);
			return $paginator;
		}
		
		$resultset = new HydratingResultSet($hydrator, $entityPrototype);
		$statement = $this->sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();
		$resultset->initialize($results);
		return $resultset;
	}

	public function getLead ($id)
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
		$hydrator->addStrategy('timecreated', new DateTimeLocalStrategy());
		$hydrator->addStrategy('timesubmitted', new DateTimeLocalStrategy());
		$hydrator->addStrategy('submitted', new BooleanStrategy());
		$lead = new LeadEntity();
		$hydrator->hydrate($result, $lead);
		
		return $lead;
	}
}
