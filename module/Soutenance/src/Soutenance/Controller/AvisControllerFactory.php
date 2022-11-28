<?php

namespace Soutenance\Controller;

use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;

class AvisControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AvisController
     */
    public function __invoke(ContainerInterface $container)
    {

        /**
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var MembreService $membreService
         * @var NotifierSoutenanceService $notifierSoutenanceService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         */
        $acteurService              = $container->get(ActeurService::class);
        $avisService                = $container->get(AvisService::class);
        $membreService              = $container->get(MembreService::class);
        $notifierSoutenanceService  = $container->get(NotifierSoutenanceService::class);
        $propositionService         = $container->get(PropositionService::class);
        $theseService               = $container->get('TheseService');
        $validationService          = $container->get(ValidationService::class);

        /**
         * @var AvisForm $avisForm
         */
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);

        /** @var AvisController $controller */
        $controller = new AvisController();
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setActeurService($acteurService);
        $controller->setNotifierSoutenanceService($notifierSoutenanceService);
        $controller->setPropositionService($propositionService);
        $controller->setAvisService($avisService);
        $controller->setMembreService($membreService);

        $controller->setAvisForm($avisForm);

        return $controller;
    }
}