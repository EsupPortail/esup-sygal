<?php

namespace Application\Form\Factory;

use Application\Form\EcoleDoctoraleForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\FormElementManager;

class EcoleDoctoraleFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var DoctrineObject $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        $form = new EcoleDoctoraleForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}