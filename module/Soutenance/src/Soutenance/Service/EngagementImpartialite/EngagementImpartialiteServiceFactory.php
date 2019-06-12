<?php

namespace Soutenance\Service\EngagementImpartialite;

use Soutenance\Service\Validation\ValidationService;
use Zend\ServiceManager\ServiceLocatorInterface;

class EngagementImpartialiteServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ValidationService $validationService */
        $validationService = $serviceLocator->get(ValidationService::class);

        /** @var EngagementImpartialiteService $service */
        $service = new EngagementImpartialiteService();
        $service->setValidationService($validationService);
        return $service;
    }
}