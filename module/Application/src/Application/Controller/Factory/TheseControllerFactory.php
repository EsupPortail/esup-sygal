<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseController;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class TheseControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return TheseController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $options = $this->getOptions($controllerManager->getServiceLocator());

        $service = new TheseController();
        $service->setTimeoutRetraitement($this->getTimeoutRetraitementFromOptions($options));

        return $service;
    }

    private function getTimeoutRetraitementFromOptions(array $options)
    {
        return isset($options['retraitement']['timeout']) ? $options['retraitement']['timeout'] : null;
    }

    private function getOptions(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('config');

        return isset($options['sodoct']) ? $options['sodoct'] : [];
    }
}