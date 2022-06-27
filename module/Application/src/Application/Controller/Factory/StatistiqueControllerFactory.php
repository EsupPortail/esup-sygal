<?php

namespace Application\Controller\Factory;

use Application\Controller\StatistiqueController;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Application\Service\These\TheseService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Interop\Container\ContainerInterface;

class StatistiqueControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService $theseService
         * @var EcoleDoctoraleService $ecoleService
         * @var EtablissementService $etabService
         * @var UniteRechercheService $uniteService
         * @var StructureService $structureService
         */
        $theseService = $container->get('TheseService');
        $ecoleService = $container->get('EcoleDoctoraleService');
        $etabService  = $container->get('EtablissementService');
        $uniteService = $container->get('UniteRechercheService');
        $structureService = $container->get(StructureService::class);

        $controller = new StatistiqueController();
        $controller->setTheseService($theseService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etabService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setStructureService($structureService);

        return $controller;
    }
}