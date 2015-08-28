<?php
namespace ApiUser\Authentication\Adapter\Factory;
use ApiUser\Authentication\Adapter\OAuth2Adapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\Exception;

class OAuth2AdapterFactory implements FactoryInterface
{

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return OAuth2Adapter
	 */
	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('Config');
		
		if (! isset($config['apiuser']['db']) || empty($config['apiuser']['db'])) {
			throw new Exception\RuntimeException(
					'The database configuration [\'apiuser\'][\'db\'] for OAuth2 is missing');
		}
		
		$username = isset($config['apiuser']['db']['username']) ? $config['apiuser']['db']['username'] : null;
		$password = isset($config['apiuser']['db']['password']) ? $config['apiuser']['db']['password'] : null;
		$options = isset($config['apiuser']['db']['options']) ? $config['apiuser']['db']['options'] : [];
		
		$connection = [
				'dsn' => $config['apiuser']['db']['dsn'],
				'username' => $username,
				'password' => $password,
				'options' => $options
		];
		
		$pdo = new \PDO($connection['dsn'], $connection['username'], 
				$connection['password'], $connection['options']);
		
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		
		return new OAuth2Adapter($pdo);
	}
}