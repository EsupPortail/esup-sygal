<?php

namespace Indicateur\Controller\Factory;

use Application\Service\AnomalieService;
use Application\Service\Etablissement\EtablissementService;
use Individu\Service\IndividuService;
use Application\Service\Structure\StructureService;
use Application\Service\These\TheseService;
use Indicateur\Controller\IndicateurController;
use Indicateur\Form\IndicateurForm;
use Indicateur\Service\IndicateurService;
use Interop\Container\ContainerInterface;

class IndicateurControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return IndicateurController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var IndividuService $individuService
         * @var TheseService $theseService
         * @var AnomalieService $anomalieService
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         * @var IndicateurService $indicateurService
         */
        $individuService = $container->get(IndividuService::class);
        $theseService = $container->get('TheseService');
        $anomalieService = $container->get(AnomalieService::class);
        $etablissementService = $container->get('EtablissementService');
        $indicateurService = $container->get(IndicateurService::class);
        $structureService = $container->get(StructureService::class);

        /** @var  IndicateurForm $indicateurForm */
        $indicateurForm = $container->get('FormElementManager')->get(IndicateurForm::class);

        $controller = new IndicateurController();
        $controller->setIndividuService($individuService);
        $controller->setTheseService($theseService);
        $controller->setAnomalieService($anomalieService);
        $controller->setEtablissementService($etablissementService);
        $controller->setIndicateurService($indicateurService);
        $controller->setStructureService($structureService);
        $controller->setIndicateurForm($indicateurForm);

        return $controller;
    }
}