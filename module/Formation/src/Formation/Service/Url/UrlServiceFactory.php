<?php

namespace Formation\Service\Url;

use Application\RouteMatch;
use Fichier\Service\Fichier\FichierStorageService;
use Laminas\Router\RouteStackInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\StructureDocument\StructureDocumentService;
use Unicaen\Console\Console;

class UrlServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : UrlService
    {
        /**
         * @var FichierStorageService $fichierStorageService
         * @var StructureDocumentService $structureDocumentService
         */
        $fichierStorageService = $container->get(FichierStorageService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $service = new UrlService();
        $service->setFichierStorageService($fichierStorageService);
        $service->setStructureDocumentService($structureDocumentService);

        /** @var RouteStackInterface $router */
        $router = $container->get(Console::isConsole() ? 'HttpRouter' : 'Router');
        $match = $container->get('application')->getMvcEvent()->getRouteMatch();
        if ($match instanceof RouteMatch) {
            $service->setRouteMatch($match);
        }
        $service->setRouter($router);

        return $service;
    }
}