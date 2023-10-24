<?php

namespace Admission\Form\Fieldset\Individu;

use Admission\Entity\Db\Individu;
use Admission\Hydrator\IndividuHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndividuFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuFieldset
    {
        /** @var IndividuHydrator $IndividuHydrator */
        $etudiantHydrator = $container->get('HydratorManager')->get(IndividuHydrator::class);
        $fieldset = new IndividuFieldset();
        $fieldset->setHydrator($etudiantHydrator);
        $fieldset->setObject(new Individu());

        return $fieldset;
    }
}