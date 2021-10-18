<?php

namespace Application\Service\PageDeCouverture;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PageDeCouverturePdfExporterFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
        $renderer = $container->get('ViewRenderer');

        $pdcConfig = $config['sygal']['page_de_couverture'];

        $templateConfig = $pdcConfig['template'];
        $templateFilePath = $templateConfig['phtml_file_path'];
        $cssFilePath = $templateConfig['css_file_path'];

        $exporter = new PageDeCouverturePdfExporter($renderer, 'A4');
        $exporter
            ->setTemplateFilePath($templateFilePath)
            ->setCssFilePath($cssFilePath);

        return $exporter;
    }
}