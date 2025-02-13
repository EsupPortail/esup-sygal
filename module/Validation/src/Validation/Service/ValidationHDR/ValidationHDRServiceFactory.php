<?php

namespace Validation\Service\ValidationHDR;

use Psr\Container\ContainerInterface;
use Validation\Service\AbstractValidationEntityServiceFactory;

class ValidationHDRServiceFactory extends AbstractValidationEntityServiceFactory
{
    protected string $validationEntityServiceClass = ValidationHDRService::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationHDRService
    {
        /** @var ValidationHDRService $service */
        $service = parent::__invoke($container);

        return $service;
    }
}