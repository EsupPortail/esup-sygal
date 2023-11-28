<?php

namespace Admission\Controller\Validation;

use Admission\Entity\Db\TypeValidation;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\TypeValidation\TypeValidationService;
use Admission\Service\Validation\AdmissionValidationService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionValidationControllerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionValidationController
    {
        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        /** @var AdmissionValidationService $admissionValidationService */
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        /** @var TypeValidationService $typeValidationService */
        $typeValidationService = $container->get(TypeValidationService::class);

        $controller = new AdmissionValidationController();
        $controller->setTypeValidationService($typeValidationService);
        $controller->setAdmissionService($admissionService);
        $controller->setAdmissionValidationService($admissionValidationService);

        return $controller;
    }
}