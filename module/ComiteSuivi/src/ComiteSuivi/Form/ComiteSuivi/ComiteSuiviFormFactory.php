<?php

namespace ComiteSuivi\Form\ComiteSuivi;


use Interop\Container\ContainerInterface;

class ComiteSuiviFormFactory {

    /**
     * @param ContainerInterface $container
     * @return ComiteSuiviForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ComiteSuiviHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ComiteSuiviHydrator::class);

        $form = new ComiteSuiviForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}