<?php

namespace Soutenance\Form\Confidentialite;

use Zend\Form\FormElementManager;

class ConfidentialiteFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var ConfidentialiteForm $form */
        $form = new ConfidentialiteForm();
        $hydrator = $sl->get('HydratorManager')->get(ConfidentialiteHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();

        return $form;
    }
}