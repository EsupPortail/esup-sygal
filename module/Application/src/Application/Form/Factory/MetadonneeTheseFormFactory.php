<?php

namespace Application\Form\Factory;

use Application\Form\MetadonneeTheseForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\FormElementManager;

/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 29/04/16
 * Time: 09:20
 */
class MetadonneeTheseFormFactory
{
    public function __invoke(FormElementManager $formElementManager)
    {
        $sl = $formElementManager->getServiceLocator();

        /** @var DoctrineObject $hydrator */
        $hydrator = $sl->get('HydratorManager')->get('DoctrineModule\Stdlib\Hydrator\DoctrineObject');

        $form = new MetadonneeTheseForm();
        $form->setHydrator($hydrator);
        
        return $form;
    }
}