<?php

namespace HDR\Form\HDRSaisie;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HDRSaisieFormFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : HDRSaisieForm
    {
        /** @var HDRSaisieHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(HDRSaisieHydrator::class);

        $form = new HDRSaisieForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}