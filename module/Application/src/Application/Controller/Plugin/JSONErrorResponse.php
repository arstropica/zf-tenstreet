<?php
namespace Application\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Exception\DomainException;
use Zend\View\Model\JsonModel;

class JSONErrorResponse extends AbstractPlugin
{
	
	// protected $response;
	public function __construct ($controller = null)
	{
		$this->setController($controller);
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
	}

	public function methodNotAllowed ()
	{
		return $this->errorHandler(405, 'Method Not Allowed');
	}

	public function errorHandler ($code, $msg = 'An unspecified Error occurred.', $data = null)
	{
		$response = $this->getResponse();
		$response->setStatusCode($code);
		$request = $this->getRequest();
		$post = $request->getPost();
		return new JsonModel(
				array(
						'code' => $code,
						'error' => $msg,
						'data' => $data,
						"request" => $post
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

	protected function getRequest()
	{
		$controller = $this->getController();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
		return $controller->getRequest();
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