<?php

namespace Application\Form\Validator\Factory;

use Application\Form\Validator\NewEmailValidator;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class NewEmailValidatorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName = null, $options = []): NewEmailValidator
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $validator = new NewEmailValidator($options);
        $validator->setEntityManager($entityManager);

        return $validator;
    }

}