<?php

namespace Admission\Form\Fieldset\Etudiant;

use Admission\Entity\Db\Individu;
use Admission\Hydrator\IndividuHydrator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EtudiantFieldsetFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EtudiantFieldset
    {
        /** @var IndividuHydrator $IndividuHydrator */
        $etudiantHydrator = $container->get('HydratorManager')->get(IndividuHydrator::class);

        $fieldset = new EtudiantFieldset();
        $fieldset->setHydrator($etudiantHydrator);
        $fieldset->setObject(new Individu());

        return $fieldset;
    }
}