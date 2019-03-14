<?php

namespace Soutenance\Controller\Factory;

use Soutenance\Controller\ConfigurationController;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Service\Parametre\ParametreService;
use Zend\Mvc\Controller\ControllerManager;

class ConfigurationControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /** @var ParametreService $parametreService */
        $parametreService = $manager->getServiceLocator()->get(ParametreService::class);

        /** @var ConfigurationForm $configurationForm */
        $configurationForm = $manager->getServiceLocator()->get('FormElementManager')->get(ConfigurationForm::class);

        /** @var ConfigurationController $controller */
        $controller = new ConfigurationController();
        $controller->setParametreService($parametreService);
        $controller->setConfigurationForm($configurationForm);

        return $controller;
    }
}