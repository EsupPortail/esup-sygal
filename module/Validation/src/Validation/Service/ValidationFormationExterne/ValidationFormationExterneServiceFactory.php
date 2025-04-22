<?php

namespace Validation\Service\ValidationFormationExterne;

use Psr\Container\ContainerInterface;
use Validation\Service\AbstractValidationEntityServiceFactory;

class ValidationFormationExterneServiceFactory extends AbstractValidationEntityServiceFactory
{
    protected string $validationEntityServiceClass = ValidationFormationExterneService::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationFormationExterneService
    {
        /** @var ValidationFormationExterneService $service */
        $service = parent::__invoke($container);

        return $service;
    }
}