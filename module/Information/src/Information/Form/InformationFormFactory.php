<?php

namespace Information\Form;

use Zend\Form\FormElementManager;

class InformationFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var InformationHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get(InformationHydrator::class);

        $form = new InformationForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}