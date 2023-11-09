<?php

namespace Admission\Form\Fieldset\Inscription;

use Admission\Entity\Db\Inscription;
use Admission\Hydrator\Inscription\InscriptionHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class InscriptionFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionFieldset
    {
        /** @var InscriptionHydrator $inscriptionHydrator */
        $inscriptionHydrator = $container->get('HydratorManager')->get(InscriptionHydrator::class);

        $fieldset = new InscriptionFieldset();
        $fieldset->setHydrator($inscriptionHydrator);
        $fieldset->setObject(new Inscription());

        return $fieldset;
    }
}