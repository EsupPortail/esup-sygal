<?php

namespace Soutenance\Service\Url;

use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Membre\MembreService;

class UrlServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return UrlService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : UrlService
    {
        /**
         * @var PhpRenderer $renderer
         * @var MembreService $membreService
         */
        $renderer = $container->get('ViewRenderer');
        $membreService = $container->get(MembreService::class);

        $service = new UrlService();
        $service->setRenderer($renderer);
        $service->setMembreService($membreService);
        return $service;
    }
}