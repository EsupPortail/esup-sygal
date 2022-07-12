<?php

namespace Formation\Form\EnqueteCategorie;

use Interop\Container\ContainerInterface;

class EnqueteCategorieHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteCategorieHydrator
     */
    public function __invoke(ContainerInterface $container) : EnqueteCategorieHydrator
    {
        $hydrator = new EnqueteCategorieHydrator();
        return $hydrator;
    }
}