<?php

namespace Structure\Form\Factory;

use Structure\Form\EtablissementForm;
use Structure\Form\Hydrator\EtablissementHydrator;
use Interop\Container\ContainerInterface;

class EtablissementFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EtablissementHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('EtablissementHydrator');

        $form = new EtablissementForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}