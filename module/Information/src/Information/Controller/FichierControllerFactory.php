<?php

namespace  Information\Controller;

use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Information\Form\FichierForm;
use Information\Service\InformationFichierService;
use Interop\Container\ContainerInterface;

class FichierControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierController
    {
        /**
         * @var \Fichier\Service\Fichier\FichierStorageService               $fileService
         * @var FichierService            $fichierService
         * @var InformationFichierService $informationFichierService
         */
        $fileService = $container->get(FichierStorageService::class);
        $fichierService = $container->get(FichierService::class);
        $informationFichierService = $container->get(InformationFichierService::class);

        /** @var FichierForm $fichierForm */
        $fichierForm = $container->get('FormElementManager')->get(FichierForm::class);

        $controller = new FichierController();
        $controller->setFichierService($fichierService);
        $controller->setInformationFichierService($informationFichierService);
        $controller->setFichierStorageService($fileService);
        $controller->setFichierForm($fichierForm);

        return $controller;
    }
}