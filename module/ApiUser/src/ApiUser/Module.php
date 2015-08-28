<?php
namespace ApiUser;
use ApiUser\Entity\Role;
use ApiUser\Entity\User;
use ApiUser\Listener\Register as RegisterListener;
use Zend\Mvc\MvcEvent;
use ApiUser\View\UnauthorizedStrategy;
use ApiUser\Mapper;

class Module
{

	public function onBootstrap (MvcEvent $e)
	{
		$eventManager = $e->getApplication()->getEventManager();
		$eventManager->attach(new RegisterListener());
	}

	public function getConfig ()
	{
		return include __DIR__ . '/../../config/module.config.php';
	}

	public function getAutoloaderConfig ()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/../../src/' .
										 __NAMESPACE__
						)
				)
		);
	}

	public function getServiceConfig ()
	{
		return array(
				'invokables' => array(
						'ApiUser\Authentication\Adapter\Db' => 'ApiUser\Authentication\Adapter\Db',
						'ApiUser\Authentication\Storage\Db' => 'ApiUser\Authentication\Storage\Db'
				),
				'factories' => array(
						'ApiUser\Entity\Role' => function  ($sm)
						{
							return new Role();
						},
						'ApiUser\Entity\User' => function  ($sm)
						{
							return new User();
						},
						'ApiUser\View\UnauthorizedStrategy' => function  ($sm)
						{
							return new UnauthorizedStrategy();
						},
						'ApiUser\Authentication\Adapter\OAuth2Adapter' => 'ApiUser\Authentication\Adapter\Factory\OAuth2AdapterFactory',
						'apiuser_user_mapper' => function  ($sm)
						{
							$options = $sm->get('zfcuser_module_options');
							$mapper = new Mapper\User();
							$mapper->setDbAdapter(
									$sm->get('zfcuser_zend_db_adapter'));
							$entityClass = $options->getUserEntityClass();
							$mapper->setEntityPrototype(new $entityClass());
							$mapper->setHydrator(new Mapper\UserHydrator());
							$mapper->setTableName($options->getTableName());
							return $mapper;
						}
				)
		);
	}
}