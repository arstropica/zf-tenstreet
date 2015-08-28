<?php
namespace Application\Entity;

class Form
{

	protected $id;

	protected $source;

	protected $form;

	protected $companyid;

	protected $company;

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
	 * @return the $source
	 */
	public function getSource ()
	{
		return $this->source;
	}

	/**
	 *
	 * @param field_type $source        	
	 */
	public function setSource ($source)
	{
		$this->source = $source;
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
	 * @return the $companyid
	 */
	public function getCompanyid ()
	{
		return $this->companyid;
	}

	/**
	 *
	 * @param field_type $companyid        	
	 */
	public function setCompanyid ($companyid)
	{
		$this->companyid = $companyid;
		return $this;
	}

	/**
	 *
	 * @return the $company
	 */
	public function getCompany ()
	{
		return $this->company;
	}

	/**
	 *
	 * @param field_type $company        	
	 */
	public function setCompany ($company)
	{
		$this->company = $company;
		return $this;
	}
}
