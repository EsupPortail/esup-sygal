<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RdvBuHydrator;
use Application\Form\RdvBuTheseForm;
use Zend\Form\FormElementManager;

class RdvBuTheseFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var RdvBuHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('RdvBuHydrator');

        $form = new RdvBuTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}