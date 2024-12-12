<?php

namespace Formation\Service\Exporter\Convocation;

use Fichier\Service\Fichier\FichierStorageService;
use Formation\Service\Url\UrlService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use UnicaenRenderer\Service\Rendu\RenduService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

class ConvocationExporterFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ConvocationExporter
    {
        /**
         * @var EtablissementService $etablissementService
         * @var FichierStorageService $fichierStorageService
         * @var RenduService $renduService
         * @var StructureService $structureService
         * @var UrlService $urlService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $renduService = $container->get(RenduService::class);
        $structureService = $container->get(StructureService::class);
        $urlService = $container->get(UrlService::class);
        $renderer = $container->get('ViewRenderer');

        $exporter = new ConvocationExporter($renderer, 'A4');
        $exporter->setEtablissementService($etablissementService);
        $exporter->setFichierStorageService($fichierStorageService);
        $exporter->setRenduService($renduService);
        $exporter->setStructureService($structureService);
        $exporter->setUrlService($urlService);

        /** @var TemplateVariablePluginManager $rapm */
        $rapm = $container->get(TemplateVariablePluginManager::class);
        $exporter->setTemplateVariablePluginManager($rapm);

        return $exporter;
    }
}