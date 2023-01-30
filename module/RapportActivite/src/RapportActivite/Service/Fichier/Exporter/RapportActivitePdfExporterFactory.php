<?php

namespace RapportActivite\Service\Fichier\Exporter;

use Psr\Container\ContainerInterface;

class RapportActivitePdfExporterFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RapportActivitePdfExporter
    {
        /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
        $renderer = $container->get('ViewRenderer');

        $config = $container->get('Config');

        $templateConfig = $config['rapport-activite']['template'];
        $footerScriptFilePath = $templateConfig['footer_path'];
        $bodyScriptFilePath = $templateConfig['template_path'];
        $cssFilePaths = $templateConfig['css_path'];

        $exporter = new RapportActivitePdfExporter($renderer, 'A4');
        $exporter
            ->setTemplateFilePath($bodyScriptFilePath)
            ->setFooterScript($footerScriptFilePath)
            ->setCssFilePaths($cssFilePaths);
        $exporter->setMarginTop(10);
        $exporter->setMarginBottom(25);
        $exporter->setMarginLeft(8);
        $exporter->setMarginRight(8);
        $exporter->setMarginFooter(8);

        return $exporter;
    }
}