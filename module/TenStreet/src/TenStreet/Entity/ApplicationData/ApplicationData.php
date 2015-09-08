<?php
namespace TenStreet\Entity\ApplicationData;

class ApplicationData
{

	protected $AppReferrer;

	protected $DisplayFields;

	public function getArrayCopy ()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $AppReferrer
	 */
	public function getAppReferrer ()
	{
		return $this->AppReferrer;
	}

	/**
	 *
	 * @param field_type $AppReferrer        	
	 */
	public function setAppReferrer ($AppReferrer)
	{
		$this->AppReferrer = $AppReferrer;
		return $this;
	}

	/**
	 *
	 * @return the $DisplayFields
	 */
	public function getDisplayFields ()
	{
		return $this->DisplayFields;
	}

	/**
	 *
	 * @param field_type $DisplayFields        	
	 */
	public function setDisplayFields ($DisplayFields)
	{
		$this->DisplayFields = $DisplayFields;
		return $this;
	}

	/**
	 *
	 * @param field_type $DisplayFields        	
	 */
	public function addDisplayFields ($DisplayFields)
	{
		$this->DisplayFields[] = $DisplayFields;
		return $this;
	}
}