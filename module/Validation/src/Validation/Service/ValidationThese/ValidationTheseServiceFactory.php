<?php

namespace Validation\Service\ValidationThese;

use Psr\Container\ContainerInterface;
use Validation\Service\AbstractValidationEntityServiceFactory;

class ValidationTheseServiceFactory extends AbstractValidationEntityServiceFactory
{
    protected string $validationEntityServiceClass = ValidationTheseService::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationTheseService
    {
        /** @var ValidationTheseService $service */
        $service = parent::__invoke($container);

        return $service;
    }
}