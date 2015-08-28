<?php
namespace Application\Hydrator;
use Zend\Stdlib\Hydrator\Reflection;
use Application\Entity\Attribute as AttributeEntity;

class AttributeHydrator extends Reflection
{

	public function hydrate (array $data, $object)
	{
		if (! $object instanceof AttributeEntity) {
			throw new \InvalidArgumentException(
					'Attribute Entity could not be mapped.');
		}
		
		return parent::hydrate($data, $object);
	}

	public function extract ($object)
	{
		if (! $object instanceof AttributeEntity) {
			throw new \InvalidArgumentException(
					'Attribute Entity could not be mapped.');
		}
		
		$data = parent::extract($object);
		
		return $data;
	}

	protected function mapField ($keyFrom, $keyTo, array $array)
	{
		$array[$keyTo] = $array[$keyFrom];
		unset($array[$keyFrom]);
		return $array;
	}

	protected function unsetField ($key, array $array)
	{
		unset($array[$key]);
		return $array;
	}
}	