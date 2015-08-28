<?php
namespace Application\Entity;

class Lead
{

	protected $id;

	protected $formid;

	protected $referrer;

	protected $ipaddress;

	protected $companyid;

	protected $company;

	protected $formname;

	protected $timecreated;

	protected $timesubmitted;

	protected $submitted = 0;

	protected $driverid;

	protected $lastresponse;

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
	 * @return the $formid
	 */
	public function getFormid ()
	{
		return $this->formid;
	}

	/**
	 *
	 * @param field_type $formid        	
	 */
	public function setFormid ($formid)
	{
		$this->formid = $formid;
		return $this;
	}

	/**
	 *
	 * @return the $timecreated
	 */
	public function getTimecreated ()
	{
		return $this->timecreated;
	}

	/**
	 *
	 * @param field_type $timecreated        	
	 */
	public function setTimecreated ($timecreated)
	{
		if ($timecreated) {
			$time = strtotime($timecreated);
			$this->timecreated = date('Y-m-d\TH:i:s', $time);
		} else {
			$this->timecreated = $timecreated;
		}
		
		return $this;
	}

	/**
	 *
	 * @return the $timesubmitted
	 */
	public function getTimesubmitted ()
	{
		return $this->timesubmitted;
	}

	/**
	 *
	 * @param field_type $timesubmitted        	
	 */
	public function setTimesubmitted ($timesubmitted)
	{
		if ($timesubmitted) {
			$time = strtotime($timesubmitted);
			$this->timesubmitted = date('Y-m-d\TH:i:s', $time);
		} else {
			$this->timesubmitted = $timesubmitted;
		}
		
		return $this;
	}

	/**
	 *
	 * @return the $submitted
	 */
	public function getSubmitted ()
	{
		return $this->submitted;
	}

	/**
	 *
	 * @param number $submitted        	
	 */
	public function setSubmitted ($submitted)
	{
		$this->submitted = $submitted;
		return $this;
	}

	/**
	 *
	 * @return the $lastresponse
	 */
	public function getLastresponse ()
	{
		return $this->lastresponse;
	}

	/**
	 *
	 * @param field_type $lastresponse        	
	 */
	public function setLastresponse ($lastresponse)
	{
		$this->lastresponse = $lastresponse;
		return $this;
	}

	/**
	 *
	 * @return the $referrer
	 */
	public function getReferrer ()
	{
		return $this->referrer;
	}

	/**
	 *
	 * @param field_type $referrer        	
	 */
	public function setReferrer ($referrer)
	{
		$this->referrer = $referrer;
		return $this;
	}

	/**
	 *
	 * @return the $ipaddress
	 */
	public function getIpaddress ()
	{
		return $this->ipaddress;
	}

	/**
	 *
	 * @param field_type $ipaddress        	
	 */
	public function setIpaddress ($ipaddress)
	{
		$this->ipaddress = $ipaddress;
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

	/**
	 *
	 * @return the $formname
	 */
	public function getFormname ()
	{
		return $this->formname;
	}

	/**
	 *
	 * @param field_type $formname        	
	 */
	public function setFormname ($formname)
	{
		$this->formname = $formname;
		return $this;
	}

	/**
	 *
	 * @return the $driverid
	 */
	public function getDriverid ()
	{
		return $this->driverid;
	}

	/**
	 *
	 * @param field_type $driverid        	
	 */
	public function setDriverid ($driverid)
	{
		$this->driverid = $driverid;
		return $this;
	}
}
