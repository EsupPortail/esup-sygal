<?php

namespace Soutenance\Service\Proposition\PropositionHDR;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\UserContextService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierStorageService;
use Horodatage\Service\Horodatage\HorodatageService;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Rule\PropositionJuryRule;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Structure\Service\Etablissement\EtablissementService;
use UnicaenParametre\Service\Parametre\ParametreService;

class PropositionHDRServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionHDRService
    {
        /**
         * @var EntityManager $entityManager
         * @var ActeurHDRService $acteurService
         * @var ValidationHDRService $validationService
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
        $acteurService = $container->get(ActeurHDRService::class);
        $validationService = $container->get(ValidationHDRService::class);
        $notifierService = $container->get(NotifierService::class);
        $parametreService = $container->get(ParametreService::class);
        $variableService = $container->get('VariableService');
        $fileService = $container->get(FichierStorageService::class);
        $etablissamentService = $container->get(EtablissementService::class);
        $membreService = $container->get(MembreService::class);
        $userContextService = $container->get('UserContextService');
        $horodatageService = $container->get(HorodatageService::class);

        $service = new PropositionHDRService();
        $service->setEntityManager($entityManager);
        $service->setActeurHDRService($acteurService);
        $service->setValidationHDRService($validationService);
        $service->setNotifierService($notifierService);
        $service->setParametreService($parametreService);
        $service->setVariableService($variableService);
        $service->setFichierStorageService($fileService);
        $service->setEtablissementService($etablissamentService);
        $service->setMembreService($membreService);
        $service->setUserContextService($userContextService);

        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $service->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        /** @var PropositionJuryRule $propositionJuryRule */
        $propositionJuryRule = $container->get(PropositionJuryRule::class);
        $service->setPropositionJuryRule($propositionJuryRule);

        return $service;
    }
}
