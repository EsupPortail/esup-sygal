<?php

namespace Admission\Form\Fieldset\Validation;

use Admission\Entity\Db\Validation;
use Admission\Hydrator\ValidationHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ValidationFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationFieldset
    {
        /** @var ValidationHydrator $validationHydrator */
        $validationHydrator = $container->get('HydratorManager')->get(ValidationHydrator::class);

        $fieldset = new ValidationFieldset();
        $fieldset->setHydrator($validationHydrator);
        $fieldset->setObject(new Validation());

        return $fieldset;
    }
}