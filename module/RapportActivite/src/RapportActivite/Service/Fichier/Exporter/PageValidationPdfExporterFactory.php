<?php

namespace RapportActivite\Service\Fichier\Exporter;

use Psr\Container\ContainerInterface;

class PageValidationPdfExporterFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PageValidationPdfExporter
    {
        $config = $container->get('Config');
        $pageSupplConfig = $config['rapport-activite']['page_de_validation'] ?: $config['rapport-activite']['page_de_couverture'];

        $templateConfig = $pageSupplConfig['template'];
        $templateFilePath = $templateConfig['phtml_file_path'];
        $cssFilePath = $templateConfig['css_file_path'];

        $exporter = new PageValidationPdfExporter(null, 'A4');
        $exporter
            ->setTemplateFilePath($templateFilePath)
            ->setCssFilePath($cssFilePath);

        return $exporter;
    }
}