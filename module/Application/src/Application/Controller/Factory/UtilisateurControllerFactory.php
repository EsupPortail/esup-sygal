<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Form\CreationUtilisateurForm;
use Application\Form\InitCompteForm;
use Application\Process\Utilisateur\UtilisateurProcess;
use Application\Service\Notification\ApplicationNotificationFactory;
use Application\Service\Role\RoleService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurSearchService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use Formation\Service\Session\SessionService;
use Individu\Service\IndividuServiceLocateTrait;
use Laminas\Authentication\AuthenticationService;
use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Service\Acteur\ActeurService;
use UnicaenAuthentification\Options\ModuleOptions;
use UnicaenAuthentification\Service\ShibService;
use UnicaenAuthentification\Service\User as AuthentificationUserService;
use UnicaenAuthentification\Service\User as UserService;
use UnicaenAuthentification\Service\UserContext;
use UnicaenAuthentification\Service\UserMapper;
use UnicaenAuthToken\Controller\TokenController;
use UnicaenAuthToken\Service\TokenService;

class UtilisateurControllerFactory
{
    use IndividuServiceLocateTrait;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UtilisateurController
    {
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
         * @var TokenService $tokenService
         * @var UserContextService $userContextService
         * @var UserService $userService
         * @var AuthentificationUserService $authentificationUserService
         * @var UtilisateurSearchService $utilisateurSearchService
         * @var UserMapper $userMapper
         * @var SessionService $sessionService
         */
        $acteurService = $container->get(ActeurService::class);
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get('UtilisateurService');
        $etablissementService = $container->get('EtablissementService');
        $ecoleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $tokenService = $container->get(TokenService::class);
        $structureService = $container->get(StructureService::class);
        $notifierService = $container->get(NotifierService::class);
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get(UserContext::class);
        $userService = $container->get(UserService::class);
        $authentificationUserService = $container->get(AuthentificationUserService::class);
        $utilisateurSearchService = $container->get(UtilisateurSearchService::class);
        $userMapper = $container->get('zfcuser_user_mapper');
        $sessionService = $container->get(SessionService::class);

        /**
         * @var InitCompteForm $initCompteForm
         */
        $initCompteForm = $container->get('FormElementManager')->get(InitCompteForm::class);

        /** @var CreationUtilisateurForm $creationUtilisateurForm */
        $creationUtilisateurForm = $container->get('FormElementManager')->get(CreationUtilisateurForm::class);

        /** @var AuthenticationService $authenticationService */
        $authenticationService = $container->get(AuthenticationService::class);

        /** @var ShibService $shibService */
        $shibService = $container->get(ShibService::class);

        /** @var ModuleOptions $authModuleOptions */
        $authModuleOptions = $container->get('unicaen-auth_module_options');

        $controller = new UtilisateurController();
        $controller->setActeurService($acteurService);
        $controller->setApplicationRoleService($roleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setIndividuService($this->locateIndividuService($container));
        $controller->setUniteRechercheService($uniteService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);
        $controller->setNotifierService($notifierService);
        $controller->setEntityManager($entityManager);
        $controller->setUserContextService($userContextService);
        $controller->setServiceUserContext($userContextService);
        $controller->setAuthentificationUserService($authentificationUserService);
        $controller->setShibService($shibService);
        $controller->setTokenService($tokenService);
        $controller->setAuthenticationService($authenticationService);
        $controller->setInitCompteForm($initCompteForm);
        $controller->setCreationUtilisateurForm($creationUtilisateurForm);
//        $controller->setOptions($authModuleOptions); // requis
        $controller->setAuthModuleOptions($authModuleOptions);
        $controller->setSearchService($utilisateurSearchService);
        $controller->setUserMapper($userMapper);
        $controller->setSessionService($sessionService);

        /** @var UtilisateurProcess $utilisateurProcess */
        $utilisateurProcess = $container->get(UtilisateurProcess::class);
        $controller->setUtilisateurProcess($utilisateurProcess);

        /** @var \Application\Service\Notification\ApplicationNotificationFactory $applicationNotificationFactory */
        $applicationNotificationFactory = $container->get(ApplicationNotificationFactory::class);
        $controller->setApplicationNotificationFactory($applicationNotificationFactory);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var TokenController $tokenController */
        $tokenController = $container->get('ControllerManager')->get(TokenController::class);
        $controller->listenEventsOf($tokenController);

        return $controller;
    }
}
