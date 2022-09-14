<?php

namespace These\Form\TheseSaisie;

use Doctorant\Service\DoctorantService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Service\Acteur\ActeurService;

/**
 * @deprecated
 */
class TheseSaisieHydratorFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : TheseSaisieHydrator
    {
        /**
         * @var ActeurService $acteurService
         * @var DoctorantService $doctorantService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var EtablissementService $etablissementService
         * @var QualiteService $qualiteService
         * @var UniteRechercheService $uniteRechercheService
         */
        $acteurService = $container->get(ActeurService::class);
        $doctorantService = $container->get(DoctorantService::class);
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $qualiteService = $container->get(QualiteService::class);
        $uniteRechercheService = $container->get(UniteRechercheService::class);

        $hydrator = new TheseSaisieHydrator();
        $hydrator->setActeurService($acteurService);
        $hydrator->setDoctorantService($doctorantService);
        $hydrator->setEcoleDoctoraleService($ecoleDoctoraleService);
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setQualiteService($qualiteService);
        $hydrator->setUniteRechercheService($uniteRechercheService);
        return $hydrator;
    }
}