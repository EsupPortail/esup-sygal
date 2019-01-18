<?php

namespace Soutenance\Form\Cotutelle;

use Zend\Form\FormElementManager;

class CotutelleFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var CotutelleForm $form */
        $form = new CotutelleForm();
        $hydrator = $sl->get('HydratorManager')->get(CotutelleHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}