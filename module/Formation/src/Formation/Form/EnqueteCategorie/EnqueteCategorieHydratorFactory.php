<?php

namespace Formation\Form\EnqueteCategorie;

use Interop\Container\ContainerInterface;

class EnqueteCategorieHydratorFactory {

    public function __invoke(ContainerInterface $container)
    {
        $hydrator = new EnqueteCategorieHydrator();
        return $hydrator;
    }
}