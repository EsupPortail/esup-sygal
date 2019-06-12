<?php

namespace Soutenance\Form\DateLieu;

use Zend\Form\FormElementManager;


class DateLieuFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var DateLieuForm $form */
        $form = new DateLieuForm();

        $hydrator = $sl->get('HydratorManager')->get(DateLieuHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}