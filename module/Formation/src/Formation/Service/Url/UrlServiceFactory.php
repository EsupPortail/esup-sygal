<?php

namespace Formation\Service\Url;

use Fichier\Service\Fichier\FichierStorageService;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\StructureDocument\StructureDocumentService;

class UrlServiceFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : UrlService
    {
        /**
         * @var FichierStorageService $fichierStorageService
         * @var PhpRenderer $renderer
         * @var StructureDocumentService $structureDocumentService
         */
        $fichierStorageService = $container->get(FichierStorageService::class);
        $renderer = $container->get('ViewRenderer');
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $service = new UrlService();
        $service->setFichierStorageService($fichierStorageService);
        $service->setRenderer($renderer);
        $service->setStructureDocumentService($structureDocumentService);
        return $service;
    }
}