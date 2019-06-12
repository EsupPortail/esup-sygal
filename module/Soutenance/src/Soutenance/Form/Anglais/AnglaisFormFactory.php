<?php

namespace Soutenance\Form\Anglais;

use Zend\Form\FormElementManager;

class AnglaisFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var AnglaisForm $form */
        $form = new AnglaisForm();
        $hydrator = $sl->get('HydratorManager')->get(AnglaisHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();

        return $form;
    }
}