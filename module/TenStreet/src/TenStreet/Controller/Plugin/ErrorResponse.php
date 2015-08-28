<?php
namespace TenStreet\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\JsonModel;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Exception\DomainException;

class ErrorResponse extends AbstractPlugin
{

	protected $event;

	public function __construct ($controller = null)
	{
		$this->setController($controller);
	}

	public function unknownError ()
	{
		return $this->errorHandler(422, 'Your request could not be processed.');
	}

	public function insufficientAuthorization ()
	{
		return $this->errorHandler(401, 'Not Authorized.');
	}

	public function missingParameter ()
	{
		return $this->errorHandler(422, 'One or more parameters are missing.');
	}

	public function methodNotAllowed ()
	{
		return $this->errorHandler(405, 'Method Not Allowed.');
	}

	public function errorHandler ($code, $msg = 'An unspecified Error occurred.')
	{
		$response = $this->getResponse();
		$response->setStatusCode($code);
		return new JsonModel(
				array(
						'code' => $code,
						'error' => $msg
				));
	}

	protected function getResponse ()
	{
		$controller = $this->getController();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
		return $controller->getResponse();
	}

	/**
	 * Get the event
	 *
	 * @return MvcEvent
	 * @throws DomainException if unable to find event
	 */
	protected function getEvent ()
	{
		if ($this->event) {
			return $this->event;
		}
		
		$controller = $this->getController();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
		
		$event = $controller->getEvent();
		if (! $event instanceof MvcEvent) {
			$params = $event->getParams();
			$event = new MvcEvent();
			$event->setParams($params);
		}
		$this->event = $event;
		
		return $this->event;
	}
}