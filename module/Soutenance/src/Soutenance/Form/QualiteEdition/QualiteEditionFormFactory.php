<?php

namespace Soutenance\Form\QualiteEdition;

use Zend\Form\FormElementManager;

class QualiteEditionFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var QualiteEditionForm $form */
        $form = new QualiteEditionForm();
        $hydrator = $sl->get('HydratorManager')->get(QualiteEditiontHydrator::class);
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}