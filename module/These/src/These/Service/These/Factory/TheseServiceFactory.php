<?php

namespace These\Service\These\Factory;

use Application\Service\Source\SourceService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;

class TheseServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseService
    {
        /**
         * @var ActeurService       $acteurService
         * @var MembreService       $membreService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);

        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);

        $service = new TheseService();
        $service->setActeurService($acteurService);
        $service->setMembreService($membreService);
        $service->setEtablissementService($etablissementService);
        $service->setFichierStorageService($fileService);
        $service->setSourceService($sourceService);

//        $acteurService = $container->get(ActeurService::class);
//        $validationService = $container->get('ValidationService');
//        $membreService = $container->get(MembreService::class);
//        $notifierService = $container->get(NotifierService::class);
//        $fichierTheseService = $container->get('FichierTheseService');
//        $variableService = $container->get('VariableService');
//        $userContextService = $container->get('UserContextService');
//        $userService = $container->get('unicaen-auth_user_service');
//        $utilisateurService = $container->get(UtilisateurService::class);
//        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
//        $sourceService = $container->get(SourceService::class);
//        /** @var EtablissementService $etablissementService */
//        $etablissementService = $container->get(EtablissementService::class);
//        /** @var FichierStorageService $fileService */
//        $fileService = $container->get(FichierStorageService::class);
//        $service = new TheseService();
//        $service->setActeurService($acteurService);
//        $service->setValidationService($validationService);
//        $service->setMembreService($membreService);
//        $service->setNotifierService($notifierService);
//        $service->setFichierTheseService($fichierTheseService);
//        $service->setVariableService($variableService);
//        $service->setUserContextService($userContextService);
//        $service->setUserService($userService);
//        $service->setUtilisateurService($utilisateurService);
//        $service->setEtablissementService($etablissementService);
//        $service->setFichierStorageService($fileService);
//        $service->setAuthorizeService($authorizeService);
//        $service->setSourceService($sourceService);

        return $service;
    }
}
