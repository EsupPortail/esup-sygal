<?php

namespace Soutenance\Form\Avis;

use Zend\Form\FormElementManager;

class AvisFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var AvisForm $form */
        $form = new AvisForm();
//        $hydrator = $sl->get('HydratorManager')->get(ConfidentialiteHydrator::class);
//        $form->setHydrator($hydrator);
        $form->init();

        return $form;
    }
}