<?php

namespace Soutenance\Form\AdresseSoutenance;

use Interop\Container\ContainerInterface;

class AdresseSoutenanceFormFactory {

    /**
     * @param ContainerInterface $container
     * @return AdresseSoutenanceForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var AdresseSoutenanceHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(AdresseSoutenanceHydrator::class);

        /** @var AdresseSoutenanceForm $form */
        $form = new AdresseSoutenanceForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}