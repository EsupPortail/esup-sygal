<?php

namespace HDR\Form\HDRSaisie;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HDRSaisieHydratorFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : HDRSaisieHydrator
    {
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new HDRSaisieHydrator($entityManager);
    }
}