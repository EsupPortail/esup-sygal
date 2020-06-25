<?php

namespace Soutenance\Controller;

use Interop\Container\ContainerInterface;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Service\Parametre\ParametreService;

class ConfigurationControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return ConfigurationController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ParametreService $parametreService */
        $parametreService = $container->get(ParametreService::class);

        /** @var ConfigurationForm $configurationForm */
        $configurationForm = $container->get('FormElementManager')->get(ConfigurationForm::class);

        /** @var ConfigurationController $controller */
        $controller = new ConfigurationController();
        $controller->setParametreService($parametreService);
        $controller->setConfigurationForm($configurationForm);

        return $controller;
    }
}