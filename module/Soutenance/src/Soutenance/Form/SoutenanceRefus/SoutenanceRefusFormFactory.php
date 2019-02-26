<?php

namespace Soutenance\Form\SoutenanceRefus;

use Zend\Form\FormElementManager;


class SoutenanceRefusFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        /** @var SoutenanceRefusForm $form */
        $form = new SoutenanceRefusForm();

        $form->init();
        
        return $form;
    }
}