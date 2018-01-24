<?php

namespace Application\Form\Factory;

use Application\Form\UniteRechercheForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\FormElementManager;

class UniteRechercheFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var DoctrineObject $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        $form = new UniteRechercheForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}