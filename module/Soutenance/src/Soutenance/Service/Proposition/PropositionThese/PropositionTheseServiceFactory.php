<?php

namespace Soutenance\Service\Proposition\PropositionThese;

use Acteur\Service\ActeurThese\ActeurTheseService;
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
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
use Structure\Service\Etablissement\EtablissementService;
use UnicaenParametre\Service\Parametre\ParametreService;

class PropositionTheseServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionTheseService
    {
        /**
         * @var EntityManager $entityManager
         * @var ActeurTheseService $acteurService
         * @var ValidationTheseService $validationService
         * @var NotifierService $notifierService
         * @var ParametreService $parametreService
         * @var VariableService $variableService
         * @var FichierStorageService $fileService
         * @var EtablissementService $etablissamentService
         * @var MembreService $membreService
         * @var UserContextService $userContextService
         * @var HorodatageService $horodatageService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $acteurService = $container->get(ActeurTheseService::class);
        $validationService = $container->get(ValidationTheseService::class);
        $notifierService = $container->get(NotifierService::class);
        $parametreService = $container->get(ParametreService::class);
        $variableService = $container->get('VariableService');
        $fileService = $container->get(FichierStorageService::class);
        $etablissamentService = $container->get(EtablissementService::class);
        $membreService = $container->get(MembreService::class);
        $userContextService = $container->get('UserContextService');
        $horodatageService = $container->get(HorodatageService::class);

        $service = new PropositionTheseService();
        $service->setEntityManager($entityManager);
        $service->setActeurTheseService($acteurService);
        $service->setValidationTheseService($validationService);
        $service->setNotifierService($notifierService);
        $service->setParametreService($parametreService);
        $service->setVariableService($variableService);
        $service->setFichierStorageService($fileService);
        $service->setEtablissementService($etablissamentService);
        $service->setMembreService($membreService);
        $service->setUserContextService($userContextService);

        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $service->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $service;
    }
}
