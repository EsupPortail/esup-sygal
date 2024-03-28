<?php

namespace These\Fieldset\Acteur;

use Interop\Container\ContainerInterface;

class ActeurHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ActeurHydrator
    {
        return new ActeurHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}