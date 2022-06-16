<?php

namespace Application\Service\These\Factory;

use Application\Service\Acteur\ActeurService;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\FichierThese\FichierTheseService;
use Fichier\Service\File\FileService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use UnicaenAuth\Service\AuthorizeService;
use UnicaenAuth\Service\User as UserService;
use Webmozart\Assert\Assert;

class TheseServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService       $acteurService
         * @var ValidationService   $validationService
         * @var MembreService       $membreService
         * @var NotifierService     $notifierService
         * @var FichierTheseService $fichierTheseService
         * @var VariableService     $variableService
         * @var UserContextService  $userContextService
         * @var UserService         $userService
         * @var UtilisateurService  $utilisateurService
         * @var AuthorizeService    $authorizeService
         */
        $acteurService = $container->get(ActeurService::class);
        $validationService = $container->get('ValidationService');
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

        /** @var FileService $fileService */
        $fileService = $container->get(FileService::class);

        $service = new TheseService();
        $service->setActeurService($acteurService);
        $service->setValidationService($validationService);
        $service->setMembreService($membreService);
        $service->setNotifierService($notifierService);
        $service->setFichierTheseService($fichierTheseService);
        $service->setVariableService($variableService);
        $service->setUserContextService($userContextService);
        $service->setUserService($userService);
        $service->setUtilisateurService($utilisateurService);
        $service->setEtablissementService($etablissementService);
        $service->setFileService($fileService);
        $service->setAuthorizeService($authorizeService);

        $this->injectConfig($service, $container);

        return $service;
    }

    private function injectConfig(TheseService $service, ContainerInterface $container)
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
