<?php

namespace Application\Form\View\Helper\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Form\View\Helper\LeadFormElement;

/**
 * Factory to inject the ModuleOptions hard dependency
 *
 * @license MIT
 */
class LeadFormElementFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->getServiceLocator()->get('Application\Options\ModuleOptions');
        return new LeadFormElement($options);
    }
}