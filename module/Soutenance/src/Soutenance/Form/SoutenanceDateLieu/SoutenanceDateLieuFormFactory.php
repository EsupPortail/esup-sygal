<?php

namespace Soutenance\Form\SoutenanceDateLieu;

use Zend\Form\FormElementManager;


class SoutenanceDateLieuFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var SoutenanceDateLieuForm $form */
        $form = new SoutenanceDateLieuForm();

        $hydrator = $sl->get('HydratorManager')->get(SoutenanceDateLieuHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}