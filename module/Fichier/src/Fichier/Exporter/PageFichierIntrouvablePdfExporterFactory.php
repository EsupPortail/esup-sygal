<?php

namespace Fichier\Exporter;

use Psr\Container\ContainerInterface;

class PageFichierIntrouvablePdfExporterFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PageFichierIntrouvablePdfExporter
    {
        $config = $container->get('Config');
        $pageConfig = $config['fichier']['page_fichier_introuvable'];

        $templateConfig = $pageConfig['template'];
        $templateFilePath = $templateConfig['phtml_file_path'];
        $cssFilePath = $templateConfig['css_file_path'];

        $exporter = new PageFichierIntrouvablePdfExporter(null, 'A4');
        $exporter
            ->setTemplateFilePath($templateFilePath)
            ->setCssFilePath($cssFilePath);

        return $exporter;
    }
}