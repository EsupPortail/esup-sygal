<?php

namespace Application\Form\Factory;

use Application\Form\CreationUtilisateurForm;
use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Interop\Container\ContainerInterface;

class CreationUtilisateurFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new CreationUtilisateurForm();

        $hydrator = $container->get('HydratorManager')->get(CreationUtilisateurHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}