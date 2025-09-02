<?php

namespace Application\Service\Role;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Psr\Container\ContainerInterface;

class RoleServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RoleService
    {
        /**
         * @var SourceService $sourceService
         */
        $sourceService = $container->get(SourceService::class);

        $service = new RoleService();
        $service->setSourceService($sourceService);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
