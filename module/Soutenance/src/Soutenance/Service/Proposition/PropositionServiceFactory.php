<?php

namespace Soutenance\Service\Proposition;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use These\Service\Acteur\ActeurService;
use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierStorageService;
use Application\Service\UserContextService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Notification\Service\NotifierService;
use Soutenance\Service\Validation\ValidationService;
use UnicaenParametre\Service\Parametre\ParametreService;

class PropositionServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionService
    {
        /**
         * @var EntityManager $entityManager
         * @var ActeurService $acteurService
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         * @var ParametreService $parametreService
         * @var VariableService $variableService
         * @var FichierStorageService $fileService
         * @var EtablissementService $etablissamentService
         * @var MembreService $membreService
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $acteurService = $container->get(ActeurService::class);
        $validationService = $container->get(ValidationService::class);
        $notifierService = $container->get(NotifierService::class);
        $parametreService = $container->get(ParametreService::class);
        $variableService = $container->get('VariableService');
        $fileService = $container->get(FichierStorageService::class);
        $etablissamentService = $container->get(EtablissementService::class);
        $membreService = $container->get(MembreService::class);
        $userContextService = $container->get('UserContextService');

        $service = new PropositionService();
        $service->setEntityManager($entityManager);
        $service->setActeurService($acteurService);
        $service->setValidationService($validationService);
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
