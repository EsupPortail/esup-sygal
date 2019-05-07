<?php

namespace Soutenance\Form\LabelEuropeen;

use Zend\Form\FormElementManager;

class LabelEuropeenFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var LabelEuropeenForm $form */
        $form = new LabelEuropeenForm();
        $hydrator = $sl->get('HydratorManager')->get(LabelEuropeenHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();

        return $form;
    }
}