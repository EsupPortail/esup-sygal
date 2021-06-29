<?php

namespace Formation\Service\Module;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class ModuleServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new ModuleService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}