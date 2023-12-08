<?php
namespace Admission\Controller\Document;

use Admission\Service\Admission\AdmissionService;
use Admission\Service\Document\DocumentService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DocumentControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DocumentController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentController
    {
        $admissionService = $container->get(AdmissionService::class);
        $natureFichierService = $container->get(NatureFichierService::class);
        $fichierService = $container->get(FichierService::class);
        $documentService = $container->get(DocumentService::class);
        $versionFichierService = $container->get(VersionFichierService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);

        $controller = new DocumentController();
        $controller->setAdmissionService($admissionService);
        $controller->setNatureFichierService($natureFichierService);
        $controller->setFichierService($fichierService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setDocumentService($documentService);
        $controller->setVersionFichierService($versionFichierService);

        return $controller;
    }
}