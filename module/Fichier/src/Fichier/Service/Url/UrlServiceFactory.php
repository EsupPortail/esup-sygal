<?php

namespace Fichier\Service\Url;

use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Structure\Service\Etablissement\EtablissementService;

class UrlServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UrlService
    {
        $service = new UrlService();

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        /** @var FichierStorageService $fichierStorageService */
        $fichierStorageService = $container->get(FichierStorageService::class);
        $service->setFichierStorageService($fichierStorageService);

        return $service;
    }
}