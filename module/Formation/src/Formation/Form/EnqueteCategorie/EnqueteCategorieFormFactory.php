<?php

namespace Formation\Form\EnqueteCategorie;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EnqueteCategorieFormFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteCategorieForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : EnqueteCategorieForm
    {
        /** @var EnqueteCategorieHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(EnqueteCategorieHydrator::class);

        $form = new EnqueteCategorieForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}