<?php

namespace Application\Form\Factory;

use Application\Form\CreationUtilisateurFromIndividuForm;
use Application\Form\Hydrator\CreationUtilisateurFromIndividuHydrator;
use Zend\Form\FormElementManager;

class CreationUtilisateurFromIndividuFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        $form = new CreationUtilisateurFromIndividuForm();

        $hydrator = $sl->get('HydratorManager')->get(CreationUtilisateurFromIndividuHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}