<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Form\CreationUtilisateurForm;
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
use Interop\Container\ContainerInterface;
use UnicaenAuth\Options\ModuleOptions;
use UnicaenAuth\Service\ShibService;
use UnicaenAuth\Service\User as UserService;
use UnicaenAuth\Service\UserContext;
use Zend\Authentication\AuthenticationService;

class UtilisateurControllerFactory
{
    use IndividuServiceLocateTrait;

    public function __invoke(ContainerInterface $container)
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
         * @var UserContextService $userContextService
         * @var UserService $userService
         */
        $acteurService = $container->get(ActeurService::class);
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get('UtilisateurService');
        $etablissementService = $container->get('EtablissementService');
        $ecoleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $structureService = $container->get(StructureService::class);
        $notifierService = $container->get(NotifierService::class);
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get(UserContext::class);
        $userService = $container->get('unicaen-auth_user_service');

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
        $controller->setAuthenticationService($authenticationService);
        $controller->setInitCompteForm($initCompteForm);
        $controller->setCreationUtilisateurForm($creationUtilisateurForm);
        $controller->setAuthModuleOptions($authModuleOptions);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}
