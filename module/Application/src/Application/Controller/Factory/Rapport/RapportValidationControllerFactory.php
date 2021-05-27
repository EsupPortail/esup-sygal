<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportValidationController;
use Application\Service\Rapport\RapportService;
use Application\Service\RapportValidation\RapportValidationService;
use Application\Service\Validation\ValidationService;
use Interop\Container\ContainerInterface;

class RapportValidationControllerFactory
{
    public function __invoke(ContainerInterface $container): RapportValidationController
    {
        $rapportService = $container->get(RapportService::class);
        $rapportValidationService = $container->get(RapportValidationService::class);
        $validationService = $container->get(ValidationService::class);

        $controller = new RapportValidationController();
        $controller->setRapportService($rapportService);
        $controller->setRapportValidationService($rapportValidationService);
        $controller->setValidationService($validationService);

        return $controller;
    }
}