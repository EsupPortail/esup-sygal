<?php

namespace Soutenance\Service\EngagementImpartialite;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Validation\ValidationService;

class EngagementImpartialiteServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return EngagementImpartialiteService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);

        /** @var EngagementImpartialiteService $service */
        $service = new EngagementImpartialiteService();
        $service->setValidationService($validationService);
        return $service;
    }
}