<?php

namespace Soutenance\Controller;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use HDR\Service\HDRService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\These\TheseService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Proposition\PropositionService;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurTheseService $acteurService
         * @var ActeurHDRService $acteurHDRService
         * @var AvisService $avisService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var EngagementImpartialiteService $engagementService
         * @var EtablissementService $etablissementService
         * @var TheseService $theseService
         * @var HDRService $hdrService
         * @var UniteRechercheService $uniteRechercheService
         * @var UserContextService $userContextService
         */
        $acteurTheseService          = $container->get(ActeurTheseService::class);
        $acteurHDRService          = $container->get(ActeurHDRService::class);
        $avisService            = $container->get(AvisService::class);
        $ecoleDoctoraleService  = $container->get(EcoleDoctoraleService::class);
        $engagementService      = $container->get(EngagementImpartialiteService::class);
        $etablissementService   = $container->get(EtablissementService::class);
        $theseService           = $container->get('TheseService');
        $hdrService           = $container->get(HDRService::class);
        $userContextService     = $container->get('UserContextService');
        $uniteRechercheService  = $container->get(UniteRechercheService::class);
        $propositionTheseService = $container->get(PropositionTheseService::class);
        $propositionHDRService = $container->get(PropositionHDRService::class);

        $controller = new IndexController();
        $controller->setActeurTheseService($acteurTheseService);
        $controller->setActeurHDRService($acteurHDRService);
        $controller->setAvisService($avisService);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);
        $controller->setEngagementImpartialiteService($engagementService);
        $controller->setEtablissementService($etablissementService);
        $controller->setTheseService($theseService);
        $controller->setHDRService($hdrService);
        $controller->setUserContextService($userContextService);
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setPropositionTheseService($propositionTheseService);
        $controller->setPropositionHDRService($propositionHDRService);

        return $controller;
    }
}