<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\UniteRechercheHydrator;
use Application\Form\UniteRechercheForm;
use Zend\Form\FormElementManager;

class UniteRechercheFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var UniteRechercheHydrator $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('UniteRechercheHydrator');

        $form = new UniteRechercheForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}