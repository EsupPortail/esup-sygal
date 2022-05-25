<?php

namespace RapportActivite\Controller\Validation;

use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;

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
        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);

        $controller = new RapportActiviteValidationController();
        $controller->setRapportActiviteService($rapportActiviteService);
        $controller->setRapportActiviteValidationService($rapportActiviteValidationService);
        $controller->setValidationService($validationService);

        return $controller;
    }
}