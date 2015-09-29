<?php
$env = getenv('APPLICATION_ENV') ?: 'development';
return array(
		'view_manager' => array(
				'display_exceptions' => ($env == 'production') ? false : true,
				'template_map' => array(
						'error/403' => __DIR__ . '/../view/error/403.phtml',
						'oauth/receive-code' => __DIR__ .
								 '/../view/zf/auth/receive-code.phtml'
				),
				'template_path_stack' => array(
						'ZfcUser' => __DIR__ . '/../view',
						'ApiUser' => __DIR__ . '/../view'
				)
		),
		'doctrine' => array(
				'driver' => array(
						// overriding zfc-user-doctrine-orm's config
						'zfcuser_entity' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'paths' => __DIR__ . '/../src/ApiUser/Entity'
						),
						
						'orm_default' => array(
								'drivers' => array(
										'ApiUser\Entity' => 'zfcuser_entity'
								)
						)
				)
		),
		
		'zfcuser' => array(
				'table_name' => 'users',
				'new_user_default_role' => 'user',
				// telling ZfcUser to use our own class
				'user_entity_class' => 'ApiUser\Entity\User',
				// telling ZfcUserDoctrineORM to skip the entities it defines
				'enable_default_entities' => false,
				'enable_registration' => false,
				'enable_username' => true,
				'auth_adapters' => array(
						100 => 'ZfcUser\Authentication\Adapter\Db'
				),
				'use_redirect_parameter_if_present' => true,
				'auth_identity_fields' => array(
						'email'
				)
		),
		
		'bjyauthorize' => array(
				'default_role' => 'guest',
				// Using the authentication identity provider, which basically
				// reads the roles from the auth service's identity
				'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
				
				'authenticated_role' => 'user',
				
				'role_providers' => array(
						// using an object repository (entity repository) to
						// load all roles into our ACL
						'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
								'object_manager' => 'doctrine.entitymanager.orm_default',
								'role_entity_class' => 'ApiUser\Entity\Role'
						)
				),
				
				'unauthorized_strategy' => 'ApiUser\View\UnauthorizedStrategy',
				
				'guards' => array(
						'BjyAuthorize\Guard\Controller' => array(
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'index'
										),
										'roles' => array(
												'guest',
												'user',
												'moderator',
												'administrator'
										)
								),
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'login',
												'authenticate'
										),
										'roles' => array(
												'guest',
												'user',
												'moderator',
												'administrator'
										)
								),
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'register'
										),
										'roles' => array(
												// 'guest'
										)
								),
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'logout',
												'changeemail',
												'changepassword'
										),
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Application\Controller\Index',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Application\Controller\Rest',
										'roles' => array(
												'guest'
										)
								),
								// 'user',
								// 'moderator',
								// 'administrator'
								
								array(
										'controller' => 'Application\Controller\Error',
										'roles' => array(
												'guest'
										)
								),
								// 'administrator'
								
								array(
										'controller' => 'TenStreet\Controller\SoapClient',
										'roles' => array(
												'guest'
										)
								),
								// 'user',
								// 'moderator',
								// 'administrator'
								
								array(
										'controller' => 'ZF\OAuth2\Controller\Auth',
										'roles' => array(
												'guest'
										)
								)
						)
				),
				'service_manager' => array(
						'invokables' => array(
								'ApiUser\View\UnauthorizedStrategy' => 'ApiUser\View\UnauthorizedStrategy'
						),
						'factories' => array(
								'ApiUser\Entity\Role' => 'ApiUser\Entity\Role',
								'ApiUser\Entity\User' => 'ApiUser\Entity\User',
								'ApiUser\Authentication\Adapter\OAuth2Adapter' => 'ApiUser\Authentication\Adapter\Factory\OAuth2AdapterFactory'
						)
				)
		)
);