<?php

namespace Application\Controller\Factory;

use Application\Controller\TableauDeBordController;
use Application\Service\AnomalieService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;

class TableauDeBordControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TableauDeBordController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);

        /**
         * @var AnomalieService $anomalieService
         * @var EtablissementService $etablissementService
         */
        $anomalieService= $container->get(AnomalieService::class);
        $etablissementService= $container->get('EtablissementService');

        $controller = new TableauDeBordController();
        $controller->setAnomalieService($anomalieService);
        $controller->setEtablissementService($etablissementService);
        $controller->setSourceService($sourceService);

        return $controller;
    }
}
