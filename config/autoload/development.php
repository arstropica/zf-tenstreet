<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
return array(
		'service_manager' => array(
				'abstract_factories' => array(
						'Zend\\Db\\Adapter\\AdapterAbstractServiceFactory'
				),
				'factories' => array(
						'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
				)
		),
		'db' => array(
				'username' => 'devtmpad_apiuser',
				'password' => 'cary5were',
				'driver' => 'Pdo',
				'dsn' => 'mysql:dbname=devtmpad_apiuser;hostname=localhost',
				'driver_options' => array(
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
				)
		)
);
