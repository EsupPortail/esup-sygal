<?php

namespace Application\Controller\Plugin;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ForwardFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return Forward
     * @throws ServiceNotCreatedException if Controllermanager service is not found in application service locator
     */
    public function createService(ServiceLocatorInterface $plugins)
    {
        $services = $plugins->getServiceLocator();
        if (!$services instanceof ServiceLocatorInterface) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the application service manager has been injected; none found',
                __CLASS__
            ));
        }

        if (!$services->has('ControllerManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the application service manager contains a "%s" service; none found',
                __CLASS__,
                'ControllerManager'
            ));
        }
        $controllers = $services->get('ControllerManager');

        return new Forward($controllers);
    }
}
