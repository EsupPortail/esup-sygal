<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Form\CreationUtilisateurForm;
use Application\Form\InitCompteForm;
use Application\Service\Acteur\ActeurService;
use Doctorant\Service\DoctorantService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurSearchService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenAuth\Service\User as UserService;
use UnicaenAuth\Service\UserContext;
use UnicaenAuthToken\Controller\TokenController;
use UnicaenAuthToken\Service\TokenService;
use Laminas\Authentication\AuthenticationService;
use ZfcUser\Mapper\UserInterface;

class UtilisateurControllerFactory
{
    use IndividuServiceLocateTrait;

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
         * @var UtilisateurSearchService $utilisateurSearchService
         * @var UserInterface $mapper
         * @var DoctorantService $doctorantService
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
        $userService = $container->get('unicaen-auth_user_service');
        $utilisateurSearchService = $container->get(UtilisateurSearchService::class);
        $userMapper = $container->get('zfcuser_user_mapper');
        $doctorantService = $container->get(DoctorantService::class);
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
        $controller->setRoleService($roleService);
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
        $controller->setUserService($userService);
        $controller->setShibService($shibService);
        $controller->setTokenService($tokenService);
        $controller->setAuthenticationService($authenticationService);
        $controller->setInitCompteForm($initCompteForm);
        $controller->setCreationUtilisateurForm($creationUtilisateurForm);
        $controller->setOptions($authModuleOptions); // requis
        $controller->setAuthModuleOptions($authModuleOptions);
        $controller->setSearchService($utilisateurSearchService);
        $controller->setUserMapper($userMapper);
        $controller->setDoctorantService($doctorantService);
        $controller->setSessionService($sessionService);

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
