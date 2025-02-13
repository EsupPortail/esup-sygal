<?php

namespace These\Controller\Factory;

use Application\Service\MailConfirmationService;
use Application\Service\Utilisateur\UtilisateurService;
use Validation\Service\ValidationThese\ValidationTheseService;
use Depot\Service\Validation\DepotValidationService;
use Doctorant\Service\MissionEnseignement\MissionEnseignementService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Controller\TheseController;
use Acteur\Service\ActeurThese\ActeurTheseService;
use These\Service\These\TheseService;

class TheseControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return TheseController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseController
    {
        /**
         * @var ValidationTheseService       $validationService
         * @var TheseService            $theseService
         * @var UniteRechercheService   $uniteService
         * @var MailConfirmationService $mailConfirmationService
         * @var MissionEnseignementService $missionEnseignementService
         * @var UtilisateurService      $utilisateurService
         * @var ActeurTheseService           $acteurService
         * @var IndividuService         $indivdiService
         */
        $validationService = $container->get(ValidationTheseService::class);
        $theseService = $container->get('TheseService');
        $uniteService = $container->get('UniteRechercheService');
        $mailConfirmationService = $container->get('MailConfirmationService');
        $missionEnseignementService = $container->get(MissionEnseignementService::class);
        $utilisateurService = $container->get('UtilisateurService');

        $controller = new TheseController();
        $controller->setValidationTheseService($validationService);
        $controller->setTheseService($theseService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setMissionEnseignementService($missionEnseignementService);
        $controller->setUtilisateurService($utilisateurService);

        /** @var \Depot\Service\Validation\DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $controller->setDepotValidationService($depotValidationService);

        return $controller;
    }
}