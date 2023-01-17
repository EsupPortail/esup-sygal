<?php

namespace Soutenance\Service\Url;

use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UrlServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return UrlService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : UrlService
    {
        /* @var PhpRenderer $renderer  */
        $renderer = $container->get('ViewRenderer');

        $service = new UrlService();
        $service->setRenderer($renderer);
        return $service;
    }
}