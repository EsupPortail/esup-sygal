<?php

namespace Application\Form\Validator\Factory;

use Application\Form\Validator\NewEmailValidator;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class NewEmailValidatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $validator = new NewEmailValidator();
        $validator->setEntityManager($entityManager);
        return $validator;
    }

}