<?php

namespace  Information\Controller;

use Application\Service\FichierThese\FichierTheseService;
use Application\Service\File\FileService;
use Application\Service\UserContextService;
use Information\Service\InformationFichierService;
use Zend\Mvc\Controller\ControllerManager;

class FichierControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var FichierTheseService       $fichierTheseService
         * @var UserContextService        $userContextService
         * @var FileService               $fileService
         * @var InformationFichierService $informationFichierService
         */
        $fichierTheseService = $manager->getServiceLocator()->get('FichierTheseService');
        $userContextService = $manager->getServiceLocator()->get('UnicaenAuth\Service\UserContext');
        $fileService = $manager->getServiceLocator()->get(FileService::class);
        $informationFichierService = $manager->getServiceLocator()->get(InformationFichierService::class);

        $controller = new FichierController();
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setUserContextService($userContextService);
        $controller->setFileService($fileService);
        $controller->setInformationFichierService($informationFichierService);

        return $controller;
    }
}