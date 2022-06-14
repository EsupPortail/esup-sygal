<?php

namespace Structure\Form\Factory;

use Structure\Form\Hydrator\UniteRechercheHydrator;
use Structure\Form\UniteRechercheForm;
use Interop\Container\ContainerInterface;

class UniteRechercheFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UniteRechercheHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get('UniteRechercheHydrator');

        $form = new UniteRechercheForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}