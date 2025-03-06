<?php

namespace Soutenance\Service\Proposition;

use Acteur\Service\ActeurThese\ActeurTheseService;
use Horodatage\Service\Horodatage\HorodatageService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Rule\PropositionJuryRule;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierStorageService;
use Application\Service\UserContextService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
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
        $propositionTheseService = $container->get(PropositionTheseService::class);
        $propositionHDRService = $container->get(PropositionHDRService::class);

        $service = new PropositionService();
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
        $service->setPropositionTheseService($propositionTheseService);
        $service->setPropositionHDRService($propositionHDRService);

        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $service->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        /** @var PropositionJuryRule $propositionJuryRule */
        $propositionJuryRule = $container->get(PropositionJuryRule::class);
        $service->setPropositionJuryRule($propositionJuryRule);

        return $service;
    }
}
