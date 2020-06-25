<?php

namespace Application\Form\Factory;

use Application\Form\CreationUtilisateurFromIndividuForm;
use Application\Form\Hydrator\CreationUtilisateurFromIndividuHydrator;
use Interop\Container\ContainerInterface;

class CreationUtilisateurFromIndividuFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new CreationUtilisateurFromIndividuForm();

        $hydrator = $container->get('HydratorManager')->get(CreationUtilisateurFromIndividuHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}