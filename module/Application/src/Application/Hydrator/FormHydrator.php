<?php
namespace Application\Hydrator;
use Zend\Stdlib\Hydrator\Reflection;
use Application\Entity\Form as FormEntity;
use Application\Entity\Submission as SubmissionEntity;

class FormHydrator extends Reflection
{

	public function hydrate (array $data, $object)
	{
		if (! $object instanceof FormEntity) {
			throw new \InvalidArgumentException(
					'Form Entity could not be mapped.');
		}
		
		return parent::hydrate($data, $object);
	}

	public function extract ($object)
	{
		if (! $object instanceof FormEntity) {
			throw new \InvalidArgumentException(
					'Form Entity could not be mapped.');
		}
		
		return parent::extract($object);
	}

	public function extractFromSubmission ($object)
	{
		if (! $object instanceof SubmissionEntity) {
			throw new \InvalidArgumentException(
					'Submission Entity could not be mapped.');
		}
		
		$leadData = parent::extract($object->getLead());
		$formData = parent::extract($object->getForm());
		
		if (isset($leadData['referrer'])) {
			$formData['source'] = $this->getHost($leadData['referrer']);
		}
		return $formData;
	}

	public function getHost ($url)
	{
		return parse_url($url, PHP_URL_HOST);
	}
}	