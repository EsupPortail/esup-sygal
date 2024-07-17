<?php

namespace Admission\Form\Fieldset\Etudiant;

use Admission\Entity\Db\Etudiant;
use Admission\Hydrator\Etudiant\EtudiantHydrator;
use Application\Service\Pays\PaysService;
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

        /** @var PaysService $paysService */
        $paysService = $container->get(PaysService::class);
        $pays = $paysService->getPaysAsOptions();
        $fieldset->setPays($pays);

        $nationalites = $paysService->getNationalitesAsOptions();
        $fieldset->setNationalites($nationalites);

        return $fieldset;
    }
}