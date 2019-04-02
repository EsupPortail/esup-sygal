<?php

namespace Soutenance\Form\ChangementTitre;

use Zend\Form\FormElementManager;


class ChangementTitreFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var ChangementTitreForm $form */
        $form = new ChangementTitreForm();

        $hydrator = $sl->get('HydratorManager')->get(ChangementTitreHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}