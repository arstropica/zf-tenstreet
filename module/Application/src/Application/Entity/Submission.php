<?php
namespace Application\Entity;

class Submission
{

	protected $id;

	protected $lead;

	protected $detail;

	protected $form;

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
	 * @param field_type $id        	
	 */
	public function setId ($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return the $lead
	 */
	public function getLead ()
	{
		return $this->lead;
	}

	/**
	 *
	 * @param field_type $lead        	
	 */
	public function setLead ($lead)
	{
		$this->lead = $lead;
		return $this;
	}

	/**
	 *
	 * @return the $form
	 */
	public function getForm ()
	{
		return $this->form;
	}

	/**
	 *
	 * @param field_type $form        	
	 */
	public function setForm ($form)
	{
		$this->form = $form;
		return $this;
	}

	/**
	 *
	 * @return the $detail
	 */
	public function getDetail ()
	{
		return $this->detail;
	}

	/**
	 *
	 * @param field_type $detail        	
	 */
	public function setDetail ($detail)
	{
		$this->detail = $detail;
		return $this;
	}
}
