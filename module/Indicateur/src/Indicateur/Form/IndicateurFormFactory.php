<?php

namespace Indicateur\Form;

use Zend\Form\FormElementManager;

class IndicateurFormFactory {

    public function __invoke(FormElementManager $formElementManager)
    {
        $form = new IndicateurForm();
        $form->init();

        $hydrator = $formElementManager->getServiceLocator()->get('HydratorManager')->get(IndicateurHydrator::class);
        $form->setHydrator($hydrator);
        return $form;
    }

}