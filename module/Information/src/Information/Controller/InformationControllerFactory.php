<?php

namespace Information\Controller;

use Information\Form\InformationForm;
use Information\Service\InformationService;
use Interop\Container\ContainerInterface;

class InformationControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var InformationService $infromationService
         */
        $infromationService = $container->get(InformationService::class);

        /** @var InformationForm $informationForm */
        $informationForm = $container->get('FormElementManager')->get(InformationForm::class);

        $controller = new InformationController();
        $controller->setInformationService($infromationService);
        $controller->setInformationForm($informationForm);

        return $controller;
    }
}