<?php

namespace These\Form\TheseSaisie;

use Interop\Container\ContainerInterface;

class TheseSaisieHydratorFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : TheseSaisieHydrator
    {
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new TheseSaisieHydrator($entityManager);
    }
}