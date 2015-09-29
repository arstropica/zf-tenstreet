<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$env = getenv('APPLICATION_ENV') ?  : 'production';
return array(
		'controllers' => array(
				'invokables' => array(
						'Application\Controller\Index' => 'Application\Controller\IndexController',
						'Application\Controller\Error' => 'Application\Controller\ErrorController'
				),
				'factories' => array(
						'Application\Controller\Rest' => 'Application\Controller\Factory\RestControllerFactory'
				)
		),
		'router' => array(
				'routes' => array(
						'home' => array(
								'type' => 'segment',
								'options' => array(
										'route' => '/[:action][/:id][page/:page][/sort/:sort][/:order][/filter/:filter]',
										'defaults' => array(
												'__NAMESPACE__' => 'Application\Controller',
												'controller' => 'Index',
												'action' => 'index'
										),
										'constraints' => array(
												'action' => 'index|view|edit|add|export|import|submit|batchsubmit',
												'id' => '[0-9]+',
												'page' => '[0-9]+',
												'sort' => '[a-zA-Z][a-zA-Z0-9._-]*',
												'order' => 'asc|desc',
												'filter' => '[a-zA-Z][a-zA-Z0-9.=_-]*'
										)
								),
								'may_terminate' => true,
								'child_routes' => array(
										'rest-api' => array(
												'type' => 'segment',
												'options' => array(
														'route' => 'rest-api[/:action][/:id]',
														'constraints' => array(
																'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
																'id' => '[0-9]+'
														),
														'defaults' => array(
																'__NAMESPACE__' => 'Application\Controller',
																'controller' => 'Rest',
																'action' => 'index'
														)
												)
										),
										'error' => array(
												'type' => 'segment',
												'options' => array(
														'route' => 'error',
														'defaults' => array(
																'__NAMESPACE__' => 'Application\Controller',
																'controller' => 'Error',
																'action' => 'index'
														)
												)
										)
								)
						)
				)
		),
		'service_manager' => array(
				'abstract_factories' => array(
						'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
						'Zend\Log\LoggerAbstractServiceFactory'
				),
				'factories' => array(
						'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
						'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
						'Application\Options\ModuleOptions' => 'Application\Options\Factory\ModuleOptionsFactory'
				)
		),
		'translator' => array(
				'locale' => 'en_US',
				'translation_file_patterns' => array(
						array(
								'type' => 'gettext',
								'base_dir' => __DIR__ . '/../language',
								'pattern' => '%s.mo'
						)
				)
		),
		'view_manager' => array(
				'display_not_found_reason' => true,
				'display_exceptions' => ($env == 'production') ? false : true,
				'doctype' => 'HTML5',
				'not_found_template' => 'error/404',
				'exception_template' => 'error/index',
				'template_map' => array(
						'paginator-slide' => __DIR__ .
								 '/../view/layout/paginator.phtml',
								'layout/layout' => __DIR__ .
								 '/../view/layout/layout.phtml',
								'application/index/index' => __DIR__ .
								 '/../view/application/index/index.phtml',
								'error/403' => __DIR__ .
								 '/../view/error/403.phtml',
								'error/404' => __DIR__ .
								 '/../view/error/404.phtml',
								'error/index' => __DIR__ .
								 '/../view/error/index.phtml'
				),
				'template_path_stack' => array(
						__DIR__ . '/../view'
				),
				'strategies' => array(
						'ViewJsonStrategy'
				)
		),
		'view_helpers' => array(
				'invokables' => array(
						'form' => 'Application\Form\View\Helper\LeadFilterForm',
						'formRow' => 'Application\Form\View\Helper\LeadFormRow',
						'formDateRange' => 'Application\Form\View\Helper\LeadFormDateRange',
						'tableCollapse' => 'Application\View\Helper\TableCollapse'
				),
				'factories' => array(
						'formElement' => 'Application\Form\View\Helper\Factory\LeadFormElementFactory'
				)
		),
		'view_helper_config' => array(
				'flashmessenger' => array(
						'message_open_format' => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
						'message_close_string' => '</li></ul></div>',
						'message_separator_string' => '</li><li>'
				)
		),
		// Placeholder for console routes
		'console' => array(
				'router' => array(
						'routes' => array()
				)
		),
		'navigation' => array(
				'default' => array(
						array(
								'label' => 'Home',
								'route' => 'home',
								'pages' => array(
										array(
												'label' => 'List',
												'route' => 'home',
												'action' => 'index'
										),
										array(
												'label' => 'View Lead',
												'route' => 'home',
												'action' => 'view'
										),
										array(
												'label' => 'Add Lead',
												'route' => 'home',
												'action' => 'add'
										)
								)
						),
						array(
								'label' => 'Profile',
								'route' => 'zfcuser',
								'pages' => array(
										array(
												'label' => 'Your Profile',
												'route' => 'zfcuser',
												'action' => 'index'
										),
										array(
												'label' => 'Register',
												'route' => 'zfcuser/register',
												'action' => 'register'
										),
										array(
												'label' => 'Login',
												'route' => 'zfcuser/login',
												'action' => 'login'
										),
										array(
												'label' => 'Change Email',
												'route' => 'zfcuser/changeemail',
												'action' => 'changeemail'
										),
										array(
												'label' => 'Change Password',
												'route' => 'zfcuser/changepassword',
												'action' => 'changepassword'
										),
										array(
												'label' => 'Logout',
												'route' => 'zfcuser/logout',
												'action' => 'logout'
										)
								)
						)
				)
		),
		'leadform' => array(
				'ignoredViewHelpers' => array(
						'file',
						'checkbox',
						'radio',
						'submit',
						'multi_checkbox',
						'button',
						'reset'
				)
		),
		'app_options' => array(
				'exceptions_from_errors' => true,
				'recover_from_fatal' => false,
				'fatal_errors_callback' => function  ($s_msg, $s_file, $s_line)
				{
					return false;
				},
				'redirect_url' => '/error',
				
				'php_settings' => array(
						'error_reporting' => E_ALL,
						'display_errors' => 'On',
						'display_startup_errors' => 'Off'
				)
		),
		'log' => array(
				'file' => 'logs/' . date('Y-m') . '.log'
		)
);
