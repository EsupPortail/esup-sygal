<?php

namespace Admission\Form\Fieldset\Financement;

use Admission\Entity\Db\Financement;
use Admission\Hydrator\Financement\FinancementHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class FinancementFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementFieldset
    {
        /** @var FinancementHydrator $financementHydrator */
        $financementHydrator = $container->get('HydratorManager')->get(FinancementHydrator::class);

        $fieldset = new FinancementFieldset();
        $fieldset->setHydrator($financementHydrator);
        $fieldset->setObject(new Financement());

        return $fieldset;
    }
}