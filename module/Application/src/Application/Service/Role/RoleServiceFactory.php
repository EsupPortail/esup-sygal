<?php

namespace Application\Service\Role;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class RoleServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return RoleService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var SourceService $sourceService */
        $sourceService = $serviceLocator->get(SourceService::class);

        $service = new RoleService();
        $service->setSourceService($sourceService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
