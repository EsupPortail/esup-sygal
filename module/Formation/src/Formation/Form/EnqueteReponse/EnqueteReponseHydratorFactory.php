<?php

namespace Formation\Form\EnqueteReponse;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class EnqueteReponseHydratorFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $hydrator = new EnqueteReponseHydrator();
        $hydrator->setEntityManager($entitymanager);
        return $hydrator;
    }
}