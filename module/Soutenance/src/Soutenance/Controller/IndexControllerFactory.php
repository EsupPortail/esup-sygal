<?php

namespace Soutenance\Controller;

use These\Service\Acteur\ActeurService;
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
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var EngagementImpartialiteService $engagementService
         * @var EtablissementService $etablissementService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var UniteRechercheService $uniteRechercheService
         * @var UserContextService $userContextService
         */
        $acteurService          = $container->get(ActeurService::class);
        $avisService            = $container->get(AvisService::class);
        $ecoleDoctoraleService  = $container->get(EcoleDoctoraleService::class);
        $engagementService      = $container->get(EngagementImpartialiteService::class);
        $etablissementService   = $container->get(EtablissementService::class);
        $propositionService     = $container->get(PropositionService::class);
        $theseService           = $container->get('TheseService');
        $userContextService     = $container->get('UserContextService');
        $uniteRechercheService  = $container->get(UniteRechercheService::class);

        $controller = new IndexController();
        $controller->setActeurService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);
        $controller->setEngagementImpartialiteService($engagementService);
        $controller->setEtablissementService($etablissementService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);
        $controller->setUniteRechercheService($uniteRechercheService);

        return $controller;
    }
}