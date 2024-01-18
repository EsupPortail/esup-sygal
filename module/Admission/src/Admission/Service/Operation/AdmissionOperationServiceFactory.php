<?php

namespace Admission\Service\Operation;

use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\Validation\AdmissionValidationService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenAvis\Service\AvisService;

class AdmissionOperationServiceFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionOperationService
    {
        $service = new AdmissionOperationService();

        /** @var TypeValidationService $typeValidationService */
        $typeValidationService = $container->get(TypeValidationService::class);
        $service->setTypeValidationService($typeValidationService);

        /** @var AdmissionValidationService $admissionValidationService */
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $service->setAdmissionValidationService($admissionValidationService);

        /** @var AdmissionAvisService $admissionAvisService */
        $admissionAvisService = $container->get(AdmissionAvisService::class);
        $service->setAdmissionAvisService($admissionAvisService);

        /** @var \UnicaenAvis\Service\AvisService $avisService */
        $avisService = $container->get(AvisService::class);
        $service->setAvisService($avisService);

        return $service;
    }
}