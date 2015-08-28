<?php
namespace TenStreet\Entity\ApplicationData;

class ApplicationData
{

	protected $AppReferrer;

	protected $DisplayField;

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
	 * @return the $DisplayField
	 */
	public function getDisplayField ()
	{
		return $this->DisplayField;
	}

	/**
	 *
	 * @param field_type $DisplayField        	
	 */
	public function setDisplayField ($DisplayField)
	{
		$this->DisplayField = $DisplayField;
		return $this;
	}

	/**
	 *
	 * @param field_type $DisplayField        	
	 */
	public function addDisplayField ($DisplayField)
	{
		$this->DisplayField[] = $DisplayField;
		return $this;
	}
}