<?php

namespace Application\Form\Factory;

use Application\Form\CreationUtilisateurForm;
use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Zend\Form\FormElementManager;


class CreationUtilisateurFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        $form = new CreationUtilisateurForm();

        $hydrator = $sl->get('HydratorManager')->get(CreationUtilisateurHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}