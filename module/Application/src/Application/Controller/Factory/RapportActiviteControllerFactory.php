<?php

namespace Application\Controller\Factory;

use Application\Controller\RapportActiviteController;
use Application\Form\RapportActiviteForm;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\Rapport\RapportService;
use Application\Service\These\TheseService;
use Application\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;

class RapportActiviteControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportActiviteController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var RapportService        $rapportService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var RapportActiviteForm     $rapportForm
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $rapportService = $container->get(RapportService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get('IndividuService');
        $rapportForm = $container->get('FormElementManager')->get(RapportActiviteForm::class);

        $controller = new RapportActiviteController();
        $controller->setTheseService($theseService);
        $controller->setRapportService($rapportService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setForm($rapportForm);

        $theseService->attach($controller->getEventManager());

        return $controller;
    }
}



