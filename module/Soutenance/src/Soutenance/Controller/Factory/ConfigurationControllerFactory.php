<?php

namespace Soutenance\Controller\Factory;

use Soutenance\Controller\ConfigurationController;
use Soutenance\Service\Parametre\ParametreService;
use Zend\Mvc\Controller\ControllerManager;

class ConfigurationControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /** @var ParametreService $parametreService */
        $parametreService = $manager->getServiceLocator()->get(ParametreService::class);

        /** @var ConfigurationController $controller */
        $controller = new ConfigurationController();
        $controller->setParametreService($parametreService);

        return $controller;
    }
}