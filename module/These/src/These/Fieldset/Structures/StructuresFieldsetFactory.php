<?php

namespace These\Fieldset\Structures;

use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class StructuresFieldsetFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructuresFieldset
    {
        $fieldset = new StructuresFieldset('Structures');

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

        return $fieldset;
    }
}