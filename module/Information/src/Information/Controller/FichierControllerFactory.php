<?php

namespace  Information\Controller;

use Fichier\Service\Fichier\FichierService;
use Information\Form\FichierForm;
use Information\Service\InformationFichierService;
use Interop\Container\ContainerInterface;

class FichierControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var FichierService            $fichierService
         * @var InformationFichierService $informationFichierService
         */
        $fichierService = $container->get(FichierService::class);
        $informationFichierService = $container->get(InformationFichierService::class);

        /** @var FichierForm $fichierForm */
        $fichierForm = $container->get('FormElementManager')->get(FichierForm::class);

        $controller = new FichierController();
        $controller->setFichierService($fichierService);
        $controller->setInformationFichierService($informationFichierService);
        $controller->setFichierForm($fichierForm);

        return $controller;
    }
}