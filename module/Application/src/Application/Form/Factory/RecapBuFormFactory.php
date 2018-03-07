<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\RecapBuHydrator;
use Application\Form\RecapBuForm;
use Zend\Form\FormElementManager;

class RecapBuFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        $form = new RecapBuForm();

        $hydrator = $sl->get('HydratorManager')->get('RecapBuHydrator');
        $form->setHydrator($hydrator);
        $form->init();
        
        return $form;
    }
}