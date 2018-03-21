<?php

namespace Application\Service\Validation;

use Zend\ServiceManager\ServiceLocatorInterface;

trait ValidationServiceLocateTrait
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return ValidationService
     */
    public function locateValidationService(ServiceLocatorInterface $sl)
    {
        /** @var ValidationService $service */
        $service = $sl->get('ValidationService');

        return $service;
    }
}