<?php

namespace Application\Service\PageDeCouverture;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PageDeCouverturePdfExporterFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var \Zend\View\Renderer\PhpRenderer $renderer */
        $renderer = $container->get('ViewRenderer');

        return new PageDeCouverturePdfExporter($renderer, 'A4');
    }
}