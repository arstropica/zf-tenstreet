<?php
namespace Application\Hydrator;
use Zend\Stdlib\Hydrator\Reflection;
use Application\Entity\Lead as LeadEntity;

class LeadHydrator extends Reflection
{

	public function hydrate (array $data, $object)
	{
		if (! $object instanceof LeadEntity) {
			throw new \InvalidArgumentException(
					'Lead Entity could not be mapped.');
		}
		
		return parent::hydrate($data, $object);
	}

	public function extract ($object)
	{
		if (! $object instanceof LeadEntity) {
			throw new \InvalidArgumentException(
					'Lead Entity could not be mapped.');
		}
		
		return parent::extract($object);
	}
}	