<?php

namespace Formation\Form\Seance;

use Interop\Container\ContainerInterface;

class SeanceFormFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceForm
     */
    public function __invoke(ContainerInterface $container) : SeanceForm
    {
        $hydrator = $container->get('HydratorManager')->get(SeanceHydrator::class);

        $form = new SeanceForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}