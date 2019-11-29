<?php

namespace Soutenance\Form\InitCompte;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\FormElementManager;

class InitCompteFormFactory {

    public function __invoke(FormElementManager $manager)
    {
        $sl = $manager->getServiceLocator();

        /** @var DoctrineObject $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        /** @var InitCompteForm $form */
        $form = new InitCompteForm();
        $form->setHydrator($hydrator);
        return $form;
    }
}