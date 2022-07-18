<?php

namespace Formation\Form\Seance;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SeanceFormFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceForm
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SeanceForm
    {
        $hydrator = $container->get('HydratorManager')->get(SeanceHydrator::class);

        $form = new SeanceForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}