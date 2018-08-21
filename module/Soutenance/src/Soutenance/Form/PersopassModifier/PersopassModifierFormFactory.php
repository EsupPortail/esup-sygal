<?php

namespace Soutenance\Form\PersopassModifier;

use Zend\Form\FormElementManager;


class PersopassModifierFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var PersopassModifierForm $form */
        $form = new PersopassModifierForm();

        $form->init();
        
        return $form;
    }
}