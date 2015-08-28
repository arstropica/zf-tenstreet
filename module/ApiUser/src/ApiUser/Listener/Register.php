<?php
namespace ApiUser\Listener;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;

class Register extends AbstractListenerAggregate
{

	public function attach (EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		$this->listeners[] = $sharedManager->attach('ZfcUser\Form\Register', 
				'init', array(
						$this,
						'onRegisterForm'
				));
		$this->listeners[] = $sharedManager->attach('ZfcUser\Service\User', 
				'register', array(
						$this,
						'onRegister'
				));
		$this->listeners[] = $sharedManager->attach('ZfcUser\Service\User', 
				'register.post', 
				array(
						$this,
						'onRegisterPost'
				));
	}

	public function onRegister (Event $e)
	{
		$sm = $e->getTarget()->getServiceManager();
		$em = $sm->get('doctrine.entitymanager.orm_default');
		$user = $e->getParam('user');
		$form = $e->getParam('form');
		
		$username = $form->get('username')->getValue();
		$roleId = $form->get('roleid')->getValue();
		
		if (! $roleId) {
			$config = $sm->get('config');
			$roleId = $config['zfcuser']['new_user_default_role'];
		}
		
		$criteria = array(
				'roleId' => $roleId
		);
		$role = $em->getRepository('ApiUser\Entity\Role')->findOneBy($criteria);
		
		if ($role !== null) {
			$user->addRole($role);
		}
		
		if ($username) {
			$_username = $username;
			for ($i = 0; $i < 3; $i ++) {
				if ($this->checkUserExists($e, $_username)) {
					$_username = $username . "_" . sprintf('%04d', rand(0, 9999));
				} else {
					break;
				}
			}
			if ($this->checkUserExists($e, $_username)) {
				$user->setUsername($_username);
			}
		}
		
		$user->setApiKey($user->addApiKey());
	}

	public function onRegisterPost (Event $e)
	{
		$user = $e->getParam('user');
		$form = $e->getParam('form');
		
		// Do something after user has registered
	}

	public function onRegisterForm (Event $e)
	{
		/* @var $form \ZfcUser\Form\Register */
		$form = $e->getTarget();
		$form->add(
				array(
						'name' => 'username',
						'options' => array(
								'label' => 'Username'
						),
						'attributes' => array(
								'type' => 'text'
						)
				));
		
		$form->add(
				array(
						'name' => 'roleid',
						'type' => 'Zend\Form\Element\Select',
						'required' => true,
						'options' => array(
								'label' => 'Role',
								'empty_option' => 'Choose a role',
								'value_options' => array(
										'guest' => 'Guest',
										'user' => 'User',
										'moderator' => 'Moderator',
										'administrator' => 'Administrator'
								)
						)
				));
	}

	public function checkUserExists (Event $e, $username)
	{
		$sm = $e->getTarget()->getServiceManager();
		$mapper = $sm->get('apiuser_user_mapper');
		$userObject = $mapper->findByUsername($username);
		
		return $userObject ? true : false;
	}
}