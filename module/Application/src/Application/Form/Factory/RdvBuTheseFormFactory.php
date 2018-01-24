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

        $hydrator = new RdvBuHydrator($sl->get('doctrine.entitymanager.orm_default'));

        $form = new RdvBuTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}