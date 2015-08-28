<?php
namespace Application\Entity;

class Attribute
{

	protected $id;

	protected $entity_id;

	protected $attribute_id;

	protected $attribute_name;

	protected $value;

	public function getArrayCopy ()
	{
		return get_object_vars($this);
	}

	/**
	 *
	 * @return the $id
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 *
	 * @return the $entity_id
	 */
	public function getEntityid ()
	{
		return $this->entity_id;
	}

	/**
	 *
	 * @return the $attribute_id
	 */
	public function getAttributeid ()
	{
		return $this->attribute_id;
	}

	/**
	 *
	 * @return the $attribute_name
	 */
	public function getAttributename ()
	{
		return $this->attribute_name;
	}

	/**
	 *
	 * @return the $value
	 */
	public function getValue ()
	{
		return $this->value;
	}

	/**
	 *
	 * @param field_type $id        	
	 */
	public function setId ($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @param field_type $entity_id        	
	 */
	public function setEntityid ($entity_id)
	{
		$this->entity_id = $entity_id;
		return $this;
	}

	/**
	 *
	 * @param field_type $attribute_id        	
	 */
	public function setAttributeid ($attribute_id)
	{
		$this->attribute_id = $attribute_id;
		return $this;
	}

	/**
	 *
	 * @param field_type $attribute_name        	
	 */
	public function setAttributename ($attribute_name)
	{
		$this->attribute_name = $attribute_name;
		return $this;
	}

	/**
	 *
	 * @param field_type $value        	
	 */
	public function setValue ($value)
	{
		$this->value = $value;
		return $this;
	}
}
