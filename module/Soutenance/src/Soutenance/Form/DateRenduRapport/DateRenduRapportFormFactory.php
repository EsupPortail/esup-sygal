<?php

namespace Soutenance\Form\DateRenduRapport;

use Zend\Form\FormElementManager;


class DateRenduRapportFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var DateRenduRapportForm $form */
        $form = new DateRenduRapportForm();
        $hydrator = $sl->get('HydratorManager')->get(DateRenduRapportHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}