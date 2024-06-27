<?php

namespace These\Fieldset\Structures;

use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Entity\Db\These;

class StructuresFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructuresFieldset
    {
        $fieldset = new StructuresFieldset('Structures');

        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $fieldset->setStructureService($structureService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $fieldset->setEtablissementService($etablissementService);

        /** @var EcoleDoctoraleService $ecoleDoctoraleService */
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $fieldset->setEcoleDoctoraleService($ecoleDoctoraleService);

        /** @var UniteRechercheService $uniteRechercheService */
        $uniteRechercheService = $container->get(UniteRechercheService::class);
        $fieldset->setUniteRechercheService($uniteRechercheService);

        /** @var StructuresHydrator $hydrator */
        $hydrator = $container->get('HydratorManager')->get(StructuresHydrator::class);
        $fieldset->setHydrator($hydrator);

        $fieldset->setObject(new These());

        return $fieldset;
    }
}