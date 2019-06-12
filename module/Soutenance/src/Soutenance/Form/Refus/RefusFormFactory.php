<?php

namespace Soutenance\Form\Refus;

use Zend\Form\FormElementManager;


class RefusFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        /** @var RefusForm $form */
        $form = new RefusForm();

        $form->init();
        
        return $form;
    }
}