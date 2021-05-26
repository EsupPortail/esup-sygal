<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Application\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Interop\Container\ContainerInterface;
use Soutenance\Entity\Avis;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Validation\ValidationService;

class RapporteurControllerFactory{

    /**
     * @param ContainerInterface $container
     * @return RapporteurController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var EngagementImpartialiteService $engagementService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         * @var UtilisateurService $utilisateurService
         */
        $acteurService = $container->get(ActeurService::class);
        $avisService = $container->get(AvisService::class);
        $engagementService = $container->get(EngagementImpartialiteService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get(TheseService::class);
        $fichierTheseService = $container->get('FichierTheseService');
        $notifierSoutenanceService = $container->get(NotifierSoutenanceService::class);
        $validationService = $container->get(ValidationService::class);
        $utilisateurService = $container->get(UtilisateurService::class);


        /**
         * @var AvisForm $avisForm
         */
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);

        $controller = new RapporteurController();
        $controller->setActeurService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setEngagementImpartialiteService($engagementService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setNotifierSoutenanceService($notifierSoutenanceService);
        $controller->setValidationService($validationService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setAvisForm($avisForm);
        return $controller;
    }
}