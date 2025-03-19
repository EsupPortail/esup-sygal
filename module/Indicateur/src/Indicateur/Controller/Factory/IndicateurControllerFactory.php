<?php

namespace Indicateur\Controller\Factory;

use Indicateur\Controller\IndicateurController;
use Indicateur\Form\IndicateurForm;
use Indicateur\Service\IndicateurService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use These\Service\These\TheseService;

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
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         * @var IndicateurService $indicateurService
         */
        $individuService = $container->get(IndividuService::class);
        $theseService = $container->get('TheseService');
        $etablissementService = $container->get('EtablissementService');
        $indicateurService = $container->get(IndicateurService::class);
        $structureService = $container->get(StructureService::class);

        /** @var  IndicateurForm $indicateurForm */
        $indicateurForm = $container->get('FormElementManager')->get(IndicateurForm::class);

        $controller = new IndicateurController();
        $controller->setIndividuService($individuService);
        $controller->setTheseService($theseService);
        $controller->setEtablissementService($etablissementService);
        $controller->setIndicateurService($indicateurService);
        $controller->setStructureService($structureService);
        $controller->setIndicateurForm($indicateurForm);

        return $controller;
    }
}