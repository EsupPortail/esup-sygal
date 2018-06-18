<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\PointsDeVigilanceHydrator;
use Application\Form\PointsDeVigilanceForm;
use Zend\Form\FormElementManager;

class PointsDeVigilanceFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        $form = new PointsDeVigilanceForm();

        $hydrator = $sl->get('HydratorManager')->get('PointsDeVigilanceHydrator');
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}