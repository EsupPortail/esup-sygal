<?php

namespace Application\Form\AdresseMail;

use Interop\Container\ContainerInterface;

class AdresseMailFormFactory {

    /**
     * @param ContainerInterface $container
     * @return AdresseMailForm
     */
    public function __invoke(ContainerInterface $container)
    {
        $hydrator = $container->get('HydratorManager')->get(AdresseMailHydrator::class);
        $form = new AdresseMailForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}