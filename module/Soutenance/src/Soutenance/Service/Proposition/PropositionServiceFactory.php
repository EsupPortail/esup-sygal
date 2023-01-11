<?php

namespace Soutenance\Service\Proposition;

use These\Service\Acteur\ActeurService;
use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierStorageService;
use Application\Service\UserContextService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Validation\ValidationService;

class PropositionServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return PropositionService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var ActeurService $acteurService
         * @var ValidationService $validationService
         * @var NotifierService $notifierSoutenanceService
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
        $notifierSoutenanceService = $container->get(NotifierService::class);
        $parametreService = $container->get(ParametreService::class);
        $variableService = $container->get('VariableService');
        $fileService = $container->get(FichierStorageService::class);
        $etablissamentService = $container->get(EtablissementService::class);
        $membreService = $container->get(MembreService::class);
        $userContextService = $container->get('UserContextService');

        /** @var PropositionService $service */
        $service = new PropositionService();
        $service->setEntityManager($entityManager);
        $service->setActeurService($acteurService);
        $service->setValidationService($validationService);
        $service->setSoutenanceNotifierService($notifierSoutenanceService);
        $service->setParametreService($parametreService);
        $service->setVariableService($variableService);
        $service->setFichierStorageService($fileService);
        $service->setEtablissementService($etablissamentService);
        $service->setMembreService($membreService);
        $service->setUserContextService($userContextService);

        return $service;
    }
}
