<?php
return array(
		'zf-oauth2' => array(
				'db' => array(
						'dsn' => 'mysql:dbname=apiuser;hostname=localhost',
						'username' => 'apiuser',
						'password' => 'cary5were'
				),
				'allow_implicit' => true, // default (set to true when you need to
				                           // support browser-based or mobile apps)
				'access_lifetime' => 3600, // default (set a value in seconds for
				                           // access tokens lifetime)
				'enforce_state' => true, // default
				// 'storage' => 'ZF\OAuth2\Adapter\PdoAdapter'
				'storage' => 'ApiUser\Authentication\Adapter\OAuth2Adapter'
		)
); // service name for
  // the OAuth2 storage
  // adapter


