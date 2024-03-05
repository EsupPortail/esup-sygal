<?php

namespace Admission\Form\Fieldset\Verification;

use Admission\Entity\Db\Verification;
use Admission\Hydrator\Verification\VerificationHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class VerificationFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): VerificationFieldset
    {
        /** @var VerificationHydrator $verificationHydrator */
        $verificationHydrator = $container->get('HydratorManager')->get(VerificationHydrator::class);
        $fieldset = new VerificationFieldset();
        $fieldset->setHydrator($verificationHydrator);
        $fieldset->setObject(new Verification());

        return $fieldset;
    }
}