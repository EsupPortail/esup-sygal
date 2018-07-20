<?php

namespace Soutenance\Form\SoutenanceMembre;

use Zend\Form\FormElementManager;


class SoutenanceMembreFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var SoutenanceMembreForm $form */
        $form = new SoutenanceMembreForm();

        $hydrator = $sl->get('HydratorManager')->get(SoutenanceMembreHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}