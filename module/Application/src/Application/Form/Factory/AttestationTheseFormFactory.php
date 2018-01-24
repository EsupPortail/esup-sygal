<?php

namespace Application\Form\Factory;

use Application\Form\AttestationTheseForm;
use Application\Form\Hydrator\AttestationHydrator;
use Zend\Form\FormElementManager;

class AttestationTheseFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var AttestationHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('AttestationHydrator');

        $form = new AttestationTheseForm();
        $form->setHydrator($hydrator);

        return $form;
    }
}