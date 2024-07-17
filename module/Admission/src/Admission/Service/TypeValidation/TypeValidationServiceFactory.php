<?php

namespace Admission\Service\TypeValidation;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TypeValidationServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TypeValidationService
    {
        $service = new TypeValidationService();
        return $service;
    }
}