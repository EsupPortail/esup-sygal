<?php

namespace ComiteSuivi\Form\Membre;


use Interop\Container\ContainerInterface;

class MembreFormFactory {

    /**
     * @param ContainerInterface $container
     * @return MembreForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var MembreHydrator $hydrator
         */
        $hydrator = $container->get('HydratorManager')->get(MembreHydrator::class);

        $form = new MembreForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}