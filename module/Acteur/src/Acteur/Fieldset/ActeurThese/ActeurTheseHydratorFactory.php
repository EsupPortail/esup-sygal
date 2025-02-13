<?php

namespace Acteur\Fieldset\ActeurThese;

use Interop\Container\ContainerInterface;

class ActeurTheseHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ActeurTheseHydrator
    {
        return new ActeurTheseHydrator($container->get('doctrine.entitymanager.orm_default'));
    }
}