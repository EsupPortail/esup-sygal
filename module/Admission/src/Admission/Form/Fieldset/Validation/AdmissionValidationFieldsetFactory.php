<?php

namespace Admission\Form\Fieldset\Validation;

use Admission\Entity\Db\AdmissionValidation;
use Admission\Hydrator\Validation\AdmissionValidationHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionValidationFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionValidationFieldset
    {
        /** @var AdmissionValidationHydrator $validationHydrator */
        $validationHydrator = $container->get('HydratorManager')->get(AdmissionValidationHydrator::class);

        $fieldset = new AdmissionValidationFieldset();
        $fieldset->setHydrator($validationHydrator);
        $fieldset->setObject(new AdmissionValidation());

        return $fieldset;
    }
}