<?php

namespace Depot\Form\Description;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class DescriptionTheseHydratorFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new DescriptionTheseHydrator($entityManager);
    }
}
