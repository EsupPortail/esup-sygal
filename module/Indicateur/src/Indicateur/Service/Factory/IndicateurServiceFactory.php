<?php

namespace Indicateur\Service\Factory;

use Doctrine\ORM\EntityManager;
use Indicateur\Service\IndicateurService;
use Interop\Container\ContainerInterface;

class IndicateurServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return IndicateurService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new IndicateurService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
