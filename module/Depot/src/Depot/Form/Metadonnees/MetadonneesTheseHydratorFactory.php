<?php

namespace Depot\Form\Metadonnees;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class MetadonneesTheseHydratorFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new MetadonneesTheseHydrator($entityManager);
    }
}
