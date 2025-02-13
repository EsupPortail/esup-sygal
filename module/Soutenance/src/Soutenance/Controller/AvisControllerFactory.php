<?php

namespace Soutenance\Controller;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Depot\Service\FichierHDR\FichierHDRService;
use Depot\Service\FichierThese\FichierTheseService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
use These\Service\These\TheseService;

class AvisControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AvisController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : AvisController
    {

        /**
         * @var ActeurTheseService $acteurService
         * @var ActeurHDRService $acteurHDRService
         * @var AvisService $avisService
         * @var MembreService $membreService
         * @var NotifierService $notifierSoutenanceService
         * @var PropositionTheseService $propositionTheseService
         * @var PropositionHDRService $propositionHDRService
         * @var TheseService $theseService
         * @var ValidationTheseService $validationService
         * @var ValidationHDRService $validationHDRService
         */
        $acteurTheseService          = $container->get(ActeurTheseService::class);
        $acteurHDRService          = $container->get(ActeurHDRService::class);
        $avisService                = $container->get(AvisService::class);
        $membreService              = $container->get(MembreService::class);
        $notifierSoutenanceService  = $container->get(NotifierService::class);
        $propositionTheseService    = $container->get(PropositionTheseService::class);
        $propositionHDRService      = $container->get(PropositionHDRService::class);
        $theseService               = $container->get('TheseService');
        $validationService          = $container->get(ValidationTheseService::class);
        $validationHDRService          = $container->get(ValidationHDRService::class);


        /**
         * @var FichierService $fichierService
         * @var FichierTheseService $fichierTheseService
         * @var FichierHDRService $fichierHDRService
         */
        $fichierService = $container->get(FichierService::class);
        $fichierTheseService = $container->get(FichierTheseService::class);
        $fichierHDRService = $container->get(FichierHDRService::class);

        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        /**
         * @var AvisForm $avisForm
         */
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);

        $controller = new AvisController();
        $controller->setTheseService($theseService);
        $controller->setValidationTheseService($validationService);
        $controller->setValidationHDRService($validationHDRService);
        $controller->setActeurTheseService($acteurTheseService);
        $controller->setActeurHDRService($acteurHDRService);
        $controller->setNotifierService($notifierSoutenanceService);
        $controller->setPropositionHDRService($propositionHDRService);
        $controller->setPropositionTheseService($propositionTheseService);
        $controller->setAvisService($avisService);
        $controller->setMembreService($membreService);

        $controller->setFichierService($fichierService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setFichierHDRService($fichierHDRService);
        $controller->setFichierStorageService($fileService);

        $controller->setAvisForm($avisForm);

        /** @var \Soutenance\Service\Notification\SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $controller;
    }
}