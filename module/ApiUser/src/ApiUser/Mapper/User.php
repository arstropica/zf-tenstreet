<?php
namespace ApiUser\Mapper;
use ZfcUser\Mapper\User as ZfcUserMapper;

class User extends ZfcUserMapper
{

	public function findByApiKey ($apiKey)
	{
		$select = $this->getSelect()->where(
				array(
						'apiKey' => $apiKey
				));
		
		$entity = $this->select($select)->current();
		$this->getEventManager()->trigger('find', $this, 
				array(
						'entity' => $entity
				));
		return $entity;
	}
}