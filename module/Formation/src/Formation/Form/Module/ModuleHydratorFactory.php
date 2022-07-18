<?php

namespace Formation\Form\Module;

use Interop\Container\ContainerInterface;

class ModuleHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleHydrator
     */
    public function __invoke(ContainerInterface $container) : ModuleHydrator
    {
        $hydrator = new ModuleHydrator();
        return $hydrator;
    }
}