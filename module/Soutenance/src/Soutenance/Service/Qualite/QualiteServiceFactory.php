<?php

namespace Soutenance\Service\Qualite;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class QualiteServiceFactory
{

    /**
     * @param ContainerInterface $container
     * @return QualiteService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var QualiteService $service */
        $service = new QualiteService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}