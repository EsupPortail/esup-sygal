<?php

namespace Individu\Form\IndividuCompl;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class IndividuComplHydratorFactory {

    public function __invoke(ContainerInterface $container) : IndividuComplHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $hydrator = new IndividuComplHydrator();
        $hydrator->setEntityManager($entityManager);
        return $hydrator;
    }
}