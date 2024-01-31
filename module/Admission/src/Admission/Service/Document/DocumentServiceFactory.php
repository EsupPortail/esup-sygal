<?php

namespace Admission\Service\Document;

use Admission\Service\Verification\VerificationService;
use Application\Service\UserContextService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DocumentServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return DocumentService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DocumentService
    {
        /**
         * @var UserContextService $userContextService;
         */
        $userContextService = $container->get('UserContextService');
        $fichierService = $container->get(FichierService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $verificationService = $container->get(VerificationService::class);

        $service = new DocumentService();
        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fichierStorageService);
        $service->setUserContextService($userContextService);
        $service->setVerificationService($verificationService);

        return $service;
    }
}