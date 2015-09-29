<?php
return array(
		'doctrine' => array(
				'connection' => array(
						// Default connection name
						'orm_default' => array(
								'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
								'params' => array(
										'host' => 'localhost',
										'port' => '3306',
										'dbname' => 'devtmpad_apiuser',
										'user' => 'devtmpad_apiuser',
										'password' => 'cary5were'
								)
						)
				)
		)
);