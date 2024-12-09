<?php

namespace Depot\Service\ConventionDeMiseEnLigne\Exporter;

use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;
use Fichier\Service\Url\UrlService;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use UnicaenRenderer\Service\Rendu\RenduService;

class ConventionDeMiseEnLigneExporterFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ConventionDeMiseEnLigneExporter
    {
        /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
        $renderer = $container->get('ViewRenderer');

        $exporter = new ConventionDeMiseEnLigneExporter($renderer, 'A4');

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $exporter->setEtablissementService($etablissementService);

        /** @var RenduService $renduService */
        $renduService = $container->get(RenduService::class);
        $exporter->setRenduService($renduService);

        /** @var \Fichier\Service\Url\UrlService $urlService */
        $urlService = $container->get(UrlService::class);
        $exporter->setUrlFichierService($urlService);

        /** @var TemplateVariablePluginManager $rapm */
        $rapm = $container->get(TemplateVariablePluginManager::class);
        $exporter->setTemplateVariablePluginManager($rapm);

        return $exporter;
    }
}