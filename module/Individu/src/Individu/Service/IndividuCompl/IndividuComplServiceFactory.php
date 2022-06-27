<?php

namespace Individu\Service\IndividuCompl;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class IndividuComplServiceFactory {

    public function __invoke(ContainerInterface $container) : IndividuComplService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new IndividuComplService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}