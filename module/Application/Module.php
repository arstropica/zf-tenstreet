<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;
use Application\Model\LeadTable;
use Application\Form\Element\LeadFormSelect;
use Zend\Db\TableGateway\TableGateway;
use Application\Controller\Plugin\BodyClasses;
use Application\Mapper\LeadMapper;
use Application\Mapper\SubmissionMapper;
use Application\View\Helper\FlashMessenger;

class Module implements AutoloaderProviderInterface, 
		FormElementProviderInterface
{

	public function onBootstrap (MvcEvent $e)
	{
		$sm = $e->getApplication()->getServiceManager();
		$app_config = $sm->get('config');
		$app_options = $app_config['app_options'];
		
		if (array_key_exists('recover_from_fatal', $app_options) &&
				 $app_options['recover_from_fatal']) {
			$redirect_url = $app_options['redirect_url'];
			$callback = null;
			if (array_key_exists('fatal_errors_callback', $app_options) &&
					 $app_options['fatal_errors_callback']) {
				$callback = $app_options['fatal_errors_callback'];
			}
			register_shutdown_function(
					array(
							'Application\Module',
							'handleFatalPHPErrors'
					), $redirect_url, $callback);
		}
		
		set_error_handler(
				array(
						'Application\Module',
						'handlePHPErrors'
				));
		
		foreach ($app_options['php_settings'] as $key => $value) {
			ini_set($key, $value);
		}
		
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$logger = $sm->get('Logger');
		$eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, 
				function  (MvcEvent $e) use( $logger)
				{
					$logger->info(
							'An Exception has occurred. ' .
									 $e->getResult()->exception->getMessage());
				}, - 200);
	}

	public function getConfig ()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig ()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' .
										 __NAMESPACE__
						)
				)
		);
	}

	public static function handlePHPErrors ($i_type, $s_message, $s_file, 
			$i_line)
	{
		if (! ($i_type && error_reporting())) {
			return;
		}
		
		throw new \Exception(
				"Error: " . $s_message . " in file " . $s_file . " at line " .
						 $i_line);
	}

	public static function handleFatalPHPErrors ($redirect_url, $callback = null)
	{
		if (php_sapi_name() != 'cli' && (($e = @error_get_last()) !== null) &&
				 (is_array($e))) {
			if (null != $callback) {
				$code = isset($e['type']) ? $e['type'] : 0;
				$msg = isset($e['message']) ? $e['message'] : '';
				$file = isset($e['file']) ? $e['file'] : '';
				$line = isset($e['line']) ? $e['line'] : '';
				$callback($msg, $file, $line);
			}
			header("Location: " . $redirect_url);
		}
		return false;
	}

	public function getControllerConfig ()
	{
		return array(
				'factories' => array(
						'Application\Controller\Rest' => 'Application\Controller\Factory\RestControllerFactory'
				)
		);
	}

	public function getServiceConfig ()
	{
		return array(
				'factories' => array(
						'OAuth2Server' => function  ($sm)
						{
							return $sm->get('OAuth2Factory');
						},
						'OAuth2Factory' => function  ($sm)
						{
							$oauth2Factory = $sm->get(
									'ZF\OAuth2\Service\OAuth2Server');
							$sm->setFactory('OAuth2FactoryInstance', 
									$oauth2Factory);
							return $sm->get('OAuth2FactoryInstance');
						},
						'Application\Model\LeadTable' => function  ($sm)
						{
							$tableGateway = $sm->get('LeadTableGateway');
							$table = new LeadTable($tableGateway);
							return $table;
						},
						'LeadTableGateway' => function  ($sm)
						{
							$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							return new TableGateway('lead', $dbAdapter);
						},
						'LeadSources' => function  ($sm)
						{
							$leadTable = $sm->get('Application\Model\LeadTable');
							$sites = $leadTable->getSources();
							$options = array();
							foreach ($sites as $site) {
								$options[$site['id']] = $site['source'];
							}
							return $options;
						},
						'Logger' => function  ($sm)
						{
							$config = $sm->get('config');
							$logger = new \Zend\Log\Logger();
							if (isset($config['log']['file']) &&
									 is_writable($config['log']['file'])) {
								$writer = new \Zend\Log\Writer\Stream(
										$config['log']['file']);
								$logger->addWriter($writer);
							}
							return $logger;
						},
						'BodyClass' => function  ($sm)
						{
							return new BodyClasses();
						},
						'LeadMapper' => function  ($sm)
						{
							$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							$mapper = new LeadMapper($dbAdapter);
							return $mapper;
						},
						'SubmissionMapper' => function  ($sm)
						{
							$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							$mapper = new SubmissionMapper($sm, $dbAdapter);
							return $mapper;
						}
				)
		);
	}

	public function getViewHelperConfig ()
	{
		return array(
				'invokables' => array(
						'form' => 'Application\Form\View\Helper\LeadFilterForm',
						'formRow' => 'Application\Form\View\Helper\LeadFormRow',
						'formDateRange' => 'Application\Form\View\Helper\LeadFormDateRange'
				),
				'factories' => array(
						'formElement' => 'Application\Form\View\Helper\Factory\LeadFormElementFactory',
						'flashMessenger' => function  ($sm)
						{
							$flash = $sm->getServiceLocator()
								->get('ControllerPluginManager')
								->get('flashmessenger');
							$app = $sm->getServiceLocator()->get('Application');
							$messages = new FlashMessenger($app->getRequest(), 
									$app->getMvcEvent());
							$messages->setFlashMessenger($flash);
							
							return $messages;
						}
				)
		);
	}

	public function getFormElementConfig ()
	{
		return array(
				'factories' => array(
						'SourceSelect' => function  ($sm)
						{
							$values = $sm->getServiceLocator()->get(
									'LeadSources');
							
							$opts = array(
									'label' => 'Filter Sites',
									'label_attributes' => array(
											'class' => 'sr-only'
									),
									'empty_option' => 'All Sites'
							);
							$attrs = array(
									'id' => 'sites',
									'class' => 'input-large'
							);
							$select = new LeadFormSelect('sites', $values, $opts, 
									$attrs);
							return $select;
						}
				)
		);
	}
}
