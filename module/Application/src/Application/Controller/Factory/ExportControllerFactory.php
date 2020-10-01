<?php

namespace Application\Controller\Factory;

use Application\Controller\ExportController;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class ExportControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return ExportController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var FichierTheseService   $fichierTheseService
         * @var TheseService          $theseService
         * @var TheseRechercheService $theseRechercheService
         */
        $fichierTheseService = $container->get('FichierTheseService');
        $theseService = $container->get('TheseService');
        $theseRechercheService = $container->get('TheseRechercheService');

        $controller = new ExportController();
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setTheseService($theseService);
        $controller->setTheseRechercheService($theseRechercheService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}