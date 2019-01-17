<?php

namespace Soutenance\Form\SoutenanceDateRenduRapport;

use Zend\Form\FormElementManager;


class SoutenanceDateRenduRapportFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var SoutenanceDateRenduRapportForm $form */
        $form = new SoutenanceDateRenduRapportForm();
        $hydrator = $sl->get('HydratorManager')->get(SoutenanceDateRenduRapportHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}