<?php

namespace RapportActivite\Controller\Validation;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use Validation\Service\ValidationService;

class RapportActiviteValidationControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationController
    {
        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        /** @var RapportActiviteValidationService $rapportActiviteValidationService */
        $rapportActiviteValidationService = $container->get(RapportActiviteValidationService::class);

        $controller = new RapportActiviteValidationController();
        $controller->setRapportActiviteService($rapportActiviteService);
        $controller->setRapportActiviteValidationService($rapportActiviteValidationService);

        /** @var \Validation\Service\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $controller->setValidationService($validationService);

        return $controller;
    }
}