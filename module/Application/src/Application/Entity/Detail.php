<?php
namespace Application\Entity;

class Detail
{

	protected $FirstName;

	protected $LastName;

	protected $City;

	protected $State;

	protected $Email;

	protected $Phone;

	protected $Question1;

	protected $Answer1;

	protected $Question2;

	protected $Answer2;

	protected $Question3;

	protected $Answer3;

	public function getArrayCopy ()
	{
		return get_object_vars($this);
	}

	/**
	 *
	 * @return the $FirstName
	 */
	public function getFirstName ()
	{
		return $this->FirstName;
	}

	/**
	 *
	 * @return the $LastName
	 */
	public function getLastName ()
	{
		return $this->LastName;
	}

	/**
	 *
	 * @return the $City
	 */
	public function getCity ()
	{
		return $this->City;
	}

	/**
	 *
	 * @return the $State
	 */
	public function getState ()
	{
		return $this->State;
	}

	/**
	 *
	 * @return the $Email
	 */
	public function getEmail ()
	{
		return $this->Email;
	}

	/**
	 *
	 * @return the $Phone
	 */
	public function getPhone ()
	{
		return $this->Phone;
	}

	/**
	 *
	 * @return the $Question1
	 */
	public function getQuestion1 ()
	{
		return $this->Question1;
	}

	/**
	 *
	 * @return the $Aanswer1
	 */
	public function getAnswer1 ()
	{
		return $this->Answer1;
	}

	/**
	 *
	 * @return the $Question2
	 */
	public function getQuestion2 ()
	{
		return $this->Question2;
	}

	/**
	 *
	 * @return the $Answer2
	 */
	public function getAnswer2 ()
	{
		return $this->Answer2;
	}

	/**
	 *
	 * @return the $Question3
	 */
	public function getQuestion3 ()
	{
		return $this->Question3;
	}

	/**
	 *
	 * @return the $Answer3
	 */
	public function getAnswer3 ()
	{
		return $this->Answer3;
	}

	/**
	 *
	 * @param field_type $FirstName        	
	 */
	public function setFirstName ($FirstName)
	{
		$this->FirstName = $FirstName;
		return $this;
	}

	/**
	 *
	 * @param field_type $LastName        	
	 */
	public function setLastName ($LastName)
	{
		$this->LastName = $LastName;
		return $this;
	}

	/**
	 *
	 * @param field_type $City        	
	 */
	public function setCity ($City)
	{
		$this->City = $City;
		return $this;
	}

	/**
	 *
	 * @param field_type $State        	
	 */
	public function setState ($State)
	{
		$this->State = $State;
		return $this;
	}

	/**
	 *
	 * @param field_type $Email        	
	 */
	public function setEmail ($Email)
	{
		$this->Email = $Email;
		return $this;
	}

	/**
	 *
	 * @param field_type $Phone        	
	 */
	public function setPhone ($Phone)
	{
		$this->Phone = $Phone;
		return $this;
	}

	/**
	 *
	 * @param field_type $Question1        	
	 */
	public function setQuestion1 ($Question1)
	{
		$this->Question1 = $Question1;
		return $this;
	}

	/**
	 *
	 * @param field_type $Answer1        	
	 */
	public function setAnswer1 ($Answer1)
	{
		$this->Answer1 = $Answer1;
		return $this;
	}

	/**
	 *
	 * @param field_type $Question2        	
	 */
	public function setQuestion2 ($Question2)
	{
		$this->Question2 = $Question2;
		return $this;
	}

	/**
	 *
	 * @param field_type $Answer2        	
	 */
	public function setAnswer2 ($Answer2)
	{
		$this->Answer2 = $Answer2;
		return $this;
	}

	/**
	 *
	 * @param field_type $Question3        	
	 */
	public function setQuestion3 ($Question3)
	{
		$this->Question3 = $Question3;
		return $this;
	}

	/**
	 *
	 * @param field_type $Answer3        	
	 */
	public function setAnswer3 ($Answer3)
	{
		$this->Answer3 = $Answer3;
		return $this;
	}
}
