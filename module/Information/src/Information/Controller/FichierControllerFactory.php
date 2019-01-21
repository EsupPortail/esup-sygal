<?php

namespace  Information\Controller;

use Application\Service\Fichier\FichierService;
use Application\Service\File\FileService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Information\Service\InformationFichierService;
use Zend\Mvc\Controller\ControllerManager;

class FichierControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var FichierService $fichierService
         * @var UserContextService $userContextService
         * @var FileService $fileService
         * @var InformationFichierService $informationFichierService
         */
        $fichierService = $manager->getServiceLocator()->get('FichierService');
        $userContextService = $manager->getServiceLocator()->get('UnicaenAuth\Service\UserContext');
        $fileService = $manager->getServiceLocator()->get(FileService::class);
        $informationFichierService = $manager->getServiceLocator()->get(InformationFichierService::class);

        $controller = new FichierController();
        $controller->setFichierService($fichierService);
        $controller->setUserContextService($userContextService);
        $controller->setFileService($fileService);
        $controller->setInformationFichierService($informationFichierService);

        return $controller;
    }
}