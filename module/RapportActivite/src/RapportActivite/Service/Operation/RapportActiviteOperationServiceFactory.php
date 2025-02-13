<?php

namespace RapportActivite\Service\Operation;

use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use UnicaenAvis\Service\AvisService;
use Validation\Service\ValidationService;

class RapportActiviteOperationServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteOperationService
    {
        $service = new RapportActiviteOperationService();

        /** @var \Validation\Service\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

        /** @var \RapportActivite\Service\Validation\RapportActiviteValidationService $rapportActiviteValidationService */
        $rapportActiviteValidationService = $container->get(RapportActiviteValidationService::class);
        $service->setRapportActiviteValidationService($rapportActiviteValidationService);

        /** @var \RapportActivite\Service\Avis\RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $service->setRapportActiviteAvisService($rapportActiviteAvisService);

        /** @var \UnicaenAvis\Service\AvisService $avisService */
        $avisService = $container->get(AvisService::class);
        $service->setAvisService($avisService);

        return $service;
    }
}