<?php

namespace These\Form\TheseSaisie;

use Interop\Container\ContainerInterface;

class TheseSaisieFormFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : TheseSaisieForm
    {
        /** @var TheseSaisieHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(TheseSaisieHydrator::class);

        $form = new TheseSaisieForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}