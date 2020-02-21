<?php

namespace ComiteSuivi\Form\ComiteSuivi;

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
