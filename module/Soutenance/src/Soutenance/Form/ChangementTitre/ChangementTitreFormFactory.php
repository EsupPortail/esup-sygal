<?php

namespace Soutenance\Form\ChangementTitre;

use Interop\Container\ContainerInterface;

class ChangementTitreFormFactory
{
    /**
     * @param ContainerInterface $container
     * @return ChangementTitreForm
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ChangementTitreHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(ChangementTitreHydrator::class);

        /** @var ChangementTitreForm $form */
        $form = new ChangementTitreForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}