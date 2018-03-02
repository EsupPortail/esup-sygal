<?php

namespace Application\Form\Factory;

use Application\Form\EcoleDoctoraleForm;
use Application\Form\Hydrator\EcoleDoctoraleHydrator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\FormElementManager;

class EcoleDoctoraleFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var EcoleDoctoraleHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('EcoleDoctoraleHydrator');

        $form = new EcoleDoctoraleForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}