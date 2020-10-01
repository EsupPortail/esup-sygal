<?php

namespace Soutenance\Service\Parametre;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class ParametreServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var ParametreService $service */
        $service = new ParametreService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
