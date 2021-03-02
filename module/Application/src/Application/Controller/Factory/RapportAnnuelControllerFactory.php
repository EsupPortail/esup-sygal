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
use Interop\Container\ContainerInterface;

class RapportAnnuelControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportAnnuelController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var RapportAnnuelService  $rapportAnnuelService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var RapportAnnuelForm     $rapportAnnuelForm
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $rapportAnnuelService = $container->get(RapportAnnuelService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get('IndividuService');
        $rapportAnnuelForm = $container->get('FormElementManager')->get(RapportAnnuelForm::class);

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



