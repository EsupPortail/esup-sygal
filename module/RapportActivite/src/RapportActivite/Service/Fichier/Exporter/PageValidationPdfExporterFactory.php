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
        /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
        $renderer = $container->get('ViewRenderer');

//        return new PageValidationPdfExporter($renderer, 'A4');

        $config = $container->get('Config');
        $pageSupplConfig = $config['rapport-activite']['page_de_couverture'];

        $templateConfig = $pageSupplConfig['template'];
        $templateFilePath = $templateConfig['phtml_file_path'];
        $cssFilePath = $templateConfig['css_file_path'];

        $exporter = new PageValidationPdfExporter($renderer, 'A4');
        $exporter
            ->setTemplateFilePath($templateFilePath)
            ->setCssFilePath($cssFilePath);

        return $exporter;
    }
}