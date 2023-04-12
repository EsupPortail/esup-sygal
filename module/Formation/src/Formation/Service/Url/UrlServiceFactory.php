<?php

namespace Formation\Service\Url;

use Fichier\Service\Fichier\FichierStorageService;
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
         * @var StructureDocumentService $structureDocumentService
         */
        $fichierStorageService = $container->get(FichierStorageService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $service = new UrlService();
        $service->setFichierStorageService($fichierStorageService);
        $service->setStructureDocumentService($structureDocumentService);
        return $service;
    }
}