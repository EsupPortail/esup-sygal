<?php

namespace Application\Controller\Factory;

use Application\Controller\ExportController;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\These\TheseSearchService;
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
         * @var TheseSearchService $theseSearchService
         */
        $fichierTheseService = $container->get('FichierTheseService');
        $theseService = $container->get('TheseService');
        $theseSearchService = $container->get(TheseSearchService::class);

        $controller = new ExportController();
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setTheseService($theseService);
        $controller->setTheseSearchService($theseSearchService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}