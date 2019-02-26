<?php

namespace Soutenance\Form\LabelEtAnglais;

use Zend\Form\FormElementManager;

class LabelEtAnglaisFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var LabelEtAnglaisForm $form */
        $form = new LabelEtAnglaisForm();
        $hydrator = $sl->get('HydratorManager')->get(LabelEtAnglaisHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();

        return $form;
    }
}