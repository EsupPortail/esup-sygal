<?php

namespace Application\Form\Validator\Factory;

use Application\Form\Validator\NewEmailValidator;
use Doctrine\ORM\EntityManager;
use Zend\Validator\ValidatorPluginManager;

class NewEmailValidatorFactory {

    public function __invoke(ValidatorPluginManager $validatorPluginManager)
    {

        var_dump("Dans la factory");

        /** @var EntityManager $entityManager */
        $entityManager = $validatorPluginManager->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        $validator = new NewEmailValidator();
        $validator->setEntityManager($entityManager);
        return $validator;
    }

}