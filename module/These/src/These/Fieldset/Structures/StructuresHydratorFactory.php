<?php

namespace These\Fieldset\Structures;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class StructuresHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructuresHydrator
    {
//        /**
//         * @var EntityManager $entityManager
//         */
//        $entityManager = $container->get('Doctrine\ORM\EntityManager');
//        $hydrator = new StructuresHydrator($entityManager);

        $hydrator = new StructuresHydrator();



        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        /** @var EcoleDoctoraleService $ecoleDoctoraleService */
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $hydrator->setEcoleDoctoraleService($ecoleDoctoraleService);

        /** @var UniteRechercheService $uniteRechercheService */
        $uniteRechercheService = $container->get(UniteRechercheService::class);
        $hydrator->setUniteRechercheService($uniteRechercheService);

        return $hydrator;
    }
}