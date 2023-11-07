<?php

namespace Admission\Form\Fieldset\Etudiant;

use Admission\Entity\Db\Etudiant;
use Admission\Hydrator\EtudiantHydrator;
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
        /** @var EtudiantHydrator $IndividuHydrator */
        $etudiantHydrator = $container->get('HydratorManager')->get(EtudiantHydrator::class);
        $fieldset = new EtudiantFieldset();
        $fieldset->setHydrator($etudiantHydrator);
        $fieldset->setObject(new Etudiant());

        return $fieldset;
    }
}