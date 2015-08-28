<?php
namespace Application\Hydrator;

// use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\Reflection;
use Application\Entity\Detail as DetailEntity;

class DetailHydrator extends Reflection
{

	public function hydrate (array $data, $object)
	{
		if (! $object instanceof DetailEntity) {
			throw new \InvalidArgumentException(
					'Detail Entity could not be mapped.');
		}
		$detail = array();
		foreach ($data as $attribute) {
			$detail[$attribute['attributename']] = $attribute['value'];
		}
		
		return parent::hydrate($detail, $object);
	}

	public function extract ($object)
	{
		if (! $object instanceof DetailEntity) {
			throw new \InvalidArgumentException(
					'Detail Entity could not be mapped.');
		}
		
		$data = parent::extract($object);
		return $data;
	}
}