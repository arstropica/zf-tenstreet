<?php
namespace TenStreet\Entity\PersonalData;

class PersonalData
{

	protected $PersonName;

	protected $PostalAddress;

	protected $ContactData;

	public function getArrayCopy ()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $PersonName
	 */
	public function getPersonName ()
	{
		return $this->PersonName;
	}

	/**
	 *
	 * @return the $PostalAddress
	 */
	public function getPostalAddress ()
	{
		return $this->PostalAddress;
	}

	/**
	 *
	 * @param field_type $PersonName        	
	 */
	public function setPersonName ($PersonName)
	{
		$this->PersonName = $PersonName;
		return $this;
	}

	/**
	 *
	 * @param field_type $PostalAddress        	
	 */
	public function setPostalAddress ($PostalAddress)
	{
		$this->PostalAddress = $PostalAddress;
		return $this;
	}

	/**
	 *
	 * @return the $ContactData
	 */
	public function getContactData ()
	{
		return $this->ContactData;
	}

	/**
	 *
	 * @param field_type $ContactData        	
	 */
	public function setContactData ($ContactData)
	{
		$this->ContactData = $ContactData;
		return $this;
	}
}