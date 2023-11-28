<?php

namespace Admission\Service\Operation;

use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\Validation\AdmissionValidationService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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

        return $service;
    }
}