<?php
namespace Application\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\DefaultStrategy;

class DateTimeLocalStrategy extends DefaultStrategy
{

	public function hydrate ($value)
	{
		if (empty($value) || $value === "0000-00-00 00:00:00") {
			$value = null;
		} elseif (is_string($value) && "" === $value) {
			$value = null;
		} elseif (is_string($value)) {
			return date('Y-m-d\TH:i:s', strtotime($value));
		}
		return $value;
	}
}