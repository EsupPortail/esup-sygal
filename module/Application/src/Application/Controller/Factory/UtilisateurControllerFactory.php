<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Form\InitCompteForm;
use Application\Service\Acteur\ActeurService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use UnicaenAuth\Service\UserContext;
use Zend\Mvc\Controller\ControllerManager;
use UnicaenAuth\Service\User as UserService;

class UtilisateurControllerFactory
{
    use IndividuServiceLocateTrait;

    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var ActeurService $acteurService
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var EtablissementService $etablissementService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         * @var StructureService $structureService
         * @var EntityManager $entityManager
         * @var NotifierService $notifierService
         * @var UserContextService $userContextService
         * @var UserService $userService
         */
        $acteurService = $sl->get(ActeurService::class);
        $roleService = $sl->get('RoleService');
        $utilisateurService = $sl->get('UtilisateurService');
        $etablissementService = $sl->get('EtablissementService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $uniteService = $sl->get('UniteRechercheService');
        $structureService = $sl->get(StructureService::class);
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $entityManager = $sl->get('doctrine.entitymanager.orm_default');
        $userContextService = $sl->get(UserContext::class);
        $userService = $sl->get('unicaen-auth_user_service');

        /**
         * @var InitCompteForm $initCompteForm
         */
        $initCompteForm = $sl->get('FormElementManager')->get(InitCompteForm::class);

        $controller = new UtilisateurController();
        $controller->setActeurService($acteurService);
        $controller->setRoleService($roleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setIndividuService($this->locateIndividuService($sl));
        $controller->setUniteRechercheService($uniteService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);
        $controller->setNotifierService($notifierService);
        $controller->setEntityManager($entityManager);
        $controller->setUserContextService($userContextService);
        $controller->setUserService($userService);
        $controller->setInitCompteForm($initCompteForm);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}
