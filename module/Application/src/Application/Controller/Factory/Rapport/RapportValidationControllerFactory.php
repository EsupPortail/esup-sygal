<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportValidationController;
use Application\Service\Rapport\RapportService;
use Application\Service\RapportValidation\RapportValidationService;
use Interop\Container\ContainerInterface;
use Validation\Service\ValidationService;

class RapportValidationControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportValidationController
    {
        $rapportService = $container->get(RapportService::class);
        $rapportValidationService = $container->get(RapportValidationService::class);

        $controller = new RapportValidationController();
        $controller->setRapportService($rapportService);
        $controller->setRapportValidationService($rapportValidationService);

        /** @var \Validation\Service\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $controller->setValidationService($validationService);

        return $controller;
    }
}