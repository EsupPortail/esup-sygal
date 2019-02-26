<?php

namespace Soutenance\Form\SoutenanceRefus;

use Zend\Form\FormElementManager;


class SoutenanceRefusFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var SoutenanceRefusForm $form */
        $form = new SoutenanceRefusForm();

        $form->init();
        
        return $form;
    }
}