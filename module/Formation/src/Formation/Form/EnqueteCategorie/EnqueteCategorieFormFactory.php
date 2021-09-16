<?php

namespace Formation\Form\EnqueteCategorie;

use Interop\Container\ContainerInterface;

class EnqueteCategorieFormFactory {

    public function __invoke(ContainerInterface $container)
    {
        /** @var EnqueteCategorieHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(EnqueteCategorieHydrator::class);

        $form = new EnqueteCategorieForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}