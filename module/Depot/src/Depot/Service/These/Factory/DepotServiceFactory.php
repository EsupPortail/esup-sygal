<?php

namespace Depot\Service\These\Factory;

use Application\Service\Notification\ApplicationNotificationFactory;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Depot\Service\FichierThese\FichierTheseService;
use Depot\Service\Notification\DepotNotificationFactory;
use Depot\Service\These\DepotService;
use Depot\Service\Validation\DepotValidationService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Soutenance\Service\Membre\MembreService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\Acteur\ActeurService;
use These\Service\Notification\TheseNotificationFactory;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\User as UserService;
use UnicaenPrivilege\Service\AuthorizeService;
use Webmozart\Assert\Assert;

class DepotServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DepotService
    {
        /**
         * @var ActeurService $acteurService
         * @var DepotValidationService $depotValidationService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var FichierTheseService $fichierTheseService
         * @var VariableService $variableService
         * @var UserContextService $userContextService
         * @var UserService $userService
         * @var UtilisateurService $utilisateurService
         * @var AuthorizeService $authorizeService
         */
        $acteurService = $container->get(ActeurService::class);
        $depotValidationService = $container->get(DepotValidationService::class);
        $membreService = $container->get(MembreService::class);
        $notifierService = $container->get(NotifierService::class);
        $fichierTheseService = $container->get('FichierTheseService');
        $variableService = $container->get('VariableService');
        $userContextService = $container->get('UserContextService');
        $userService = $container->get('unicaen-auth_user_service');
        $utilisateurService = $container->get(UtilisateurService::class);
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);

        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        $service = new DepotService();
        $service->setActeurService($acteurService);
        $service->setDepotValidationService($depotValidationService);
        $service->setMembreService($membreService);
        $service->setNotifierService($notifierService);
        $service->setFichierTheseService($fichierTheseService);
        $service->setVariableService($variableService);
        $service->setUserContextService($userContextService);
        $service->setUserService($userService);
        $service->setUtilisateurService($utilisateurService);
        $service->setEtablissementService($etablissementService);
        $service->setFichierStorageService($fileService);
        $service->setAuthorizeService($authorizeService);

        /** @var \These\Service\These\TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $service->setTheseService($theseService);

        /** @var \Application\Service\Notification\ApplicationNotificationFactory $applicationNotificationFactory */
        $applicationNotificationFactory = $container->get(ApplicationNotificationFactory::class);
        $service->setApplicationNotificationFactory($applicationNotificationFactory);

        /** @var \These\Service\Notification\TheseNotificationFactory $theseNotificationFactory */
        $theseNotificationFactory = $container->get(TheseNotificationFactory::class);
        $service->setTheseNotificationFactory($theseNotificationFactory);

        /** @var \Depot\Service\Notification\DepotNotificationFactory $depotNotificationFactory */
        $depotNotificationFactory = $container->get(DepotNotificationFactory::class);
        $service->setDepotNotificationFactory($depotNotificationFactory);

        $this->injectConfig($service, $container);

        return $service;
    }

    private function injectConfig(DepotService $service, ContainerInterface $container)
    {
        $config = $container->get('Config');
        Assert::keyExists($config, 'sygal');
        Assert::keyExists($config['sygal'], 'depot_version_corrigee');

        $configDepotVersionCorrigee = $config['sygal']['depot_version_corrigee'];
        Assert::keyExists($configDepotVersionCorrigee, 'resaisir_autorisation_diffusion');
        Assert::keyExists($configDepotVersionCorrigee, 'resaisir_attestations');

        $service->setResaisirAutorisationDiffusionVersionCorrigee($configDepotVersionCorrigee['resaisir_autorisation_diffusion']);
        $service->setResaisirAttestationsVersionCorrigee($configDepotVersionCorrigee['resaisir_attestations']);
    }
}
