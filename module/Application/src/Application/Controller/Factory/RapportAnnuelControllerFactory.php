<?php

namespace Application\Controller\Factory;

use Application\Controller\RapportAnnuelController;
use Application\Form\RapportAnnuelForm;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\RapportAnnuel\RapportAnnuelService;
use Application\Service\These\TheseService;
use Application\Service\VersionFichier\VersionFichierService;
use Zend\Mvc\Controller\ControllerManager;

class RapportAnnuelControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return RapportAnnuelController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $serviceLocator = $controllerManager->getServiceLocator();

        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var RapportAnnuelService  $rapportAnnuelService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var RapportAnnuelForm     $rapportAnnuelForm
         */
        $theseService = $serviceLocator->get('TheseService');
        $fichierService = $serviceLocator->get(FichierService::class);
        $rapportAnnuelService = $serviceLocator->get(RapportAnnuelService::class);
        $versionFichierService = $serviceLocator->get('VersionFichierService');
        $notificationService = $serviceLocator->get(NotifierService::class);
        $individuService = $serviceLocator->get('IndividuService');
        $rapportAnnuelForm = $serviceLocator->get('FormElementManager')->get(RapportAnnuelForm::class);

        $controller = new RapportAnnuelController();
        $controller->setTheseService($theseService);
        $controller->setRapportAnnuelService($rapportAnnuelService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setForm($rapportAnnuelForm);

        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



