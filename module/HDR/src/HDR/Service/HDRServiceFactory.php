<?php

namespace HDR\Service;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierStorageService;
use Horodatage\Service\Horodatage\HorodatageService;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Membre\MembreService;
use Structure\Service\Etablissement\EtablissementService;
use UnicaenParametre\Service\Parametre\ParametreService;
use Validation\Service\ValidationHDR\ValidationHDRService;

class HDRServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): HDRService
    {
        /**
         * @var EntityManager $entityManager
         * @var ActeurHDRService $acteurHDRService
         * @var ValidationHDRService $validationService
         * @var NotifierService $notifierService
         * @var ParametreService $parametreService
         * @var VariableService $variableService
         * @var FichierStorageService $fileService
         * @var EtablissementService $etablissamentService
         * @var MembreService $membreService
         * @var UserContextService $userContextService
         * @var HorodatageService $horodatageService
         * @var SourceService $sourceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $acteurHDRService = $container->get(ActeurHDRService::class);
        $validationService = $container->get(ValidationHDRService::class);
        $notifierService = $container->get(NotifierService::class);
        $parametreService = $container->get(ParametreService::class);
        $variableService = $container->get('VariableService');
        $fileService = $container->get(FichierStorageService::class);
        $etablissamentService = $container->get(EtablissementService::class);
        $membreService = $container->get(MembreService::class);
        $userContextService = $container->get('UserContextService');
        $horodatageService = $container->get(HorodatageService::class);
        $sourceService = $container->get(SourceService::class);

        $service = new HDRService();
        $service->setEntityManager($entityManager);
        $service->setActeurHDRService($acteurHDRService);
        $service->setValidationHDRService($validationService);
        $service->setNotifierService($notifierService);
        $service->setParametreService($parametreService);
        $service->setVariableService($variableService);
        $service->setFichierStorageService($fileService);
        $service->setEtablissementService($etablissamentService);
        $service->setMembreService($membreService);
        $service->setUserContextService($userContextService);
        $service->setSourceService($sourceService);

        return $service;
    }
}
