<?php

namespace Soutenance\Controller;

use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;

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
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var MembreService $membreService
         * @var NotifierService $notifierSoutenanceService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         */
        $acteurService              = $container->get(ActeurService::class);
        $avisService                = $container->get(AvisService::class);
        $membreService              = $container->get(MembreService::class);
        $notifierSoutenanceService  = $container->get(NotifierService::class);
        $propositionService         = $container->get(PropositionService::class);
        $theseService               = $container->get('TheseService');
        $validationService          = $container->get(ValidationService::class);

        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);

        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        /**
         * @var AvisForm $avisForm
         */
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);

        $controller = new AvisController();
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setActeurService($acteurService);
        $controller->setSoutenanceNotifierService($notifierSoutenanceService);
        $controller->setPropositionService($propositionService);
        $controller->setAvisService($avisService);
        $controller->setMembreService($membreService);

        $controller->setFichierService($fichierService);
        $controller->setFichierStorageService($fileService);

        $controller->setAvisForm($avisForm);

        return $controller;
    }
}