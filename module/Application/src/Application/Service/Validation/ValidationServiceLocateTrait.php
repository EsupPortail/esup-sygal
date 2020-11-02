<?php

namespace Application\Service\Validation;

use Interop\Container\ContainerInterface;

trait ValidationServiceLocateTrait
{
    /**
     * @param ContainerInterface $container
     * @return ValidationService
     */
    public function locateValidationService(ContainerInterface $container)
    {
        /** @var ValidationService $service */
        $service = $container->get('ValidationService');

        return $service;
    }
}