<?php

namespace Application\Form\Factory;

use Application\Form\EtablissementForm;
use Application\Form\Hydrator\EtablissementHydrator;
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