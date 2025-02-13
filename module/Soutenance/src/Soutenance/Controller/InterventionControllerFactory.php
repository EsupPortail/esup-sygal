<?php

namespace Soutenance\Controller;

use Depot\Service\FichierHDR\FichierHDRService;
use Depot\Service\FichierThese\FichierTheseService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Intervention\InterventionService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use UnicaenParametre\Service\Parametre\ParametreService;

class InterventionControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return InterventionController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : InterventionController
    {
        /**
         * @var EntityManager $entityManager
         * @var FichierTheseService $fichierTheseService
         * @var FichierHDRService $fichierHDRService
         * @var InterventionService $interventionService
         * @var JustificatifService $justificatifService
         * @var MembreService $membreService
         * @var ParametreService $parametreService
         * @var PropositionService $propositionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fichierTheseService = $container->get(FichierTheseService::class);
        $fichierHDRService = $container->get(FichierHDRService::class);
        $interventionService = $container->get(InterventionService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $membreService = $container->get(MembreService::class);
        $parametreService = $container->get(ParametreService::class);

        $controller = new InterventionController();
        $controller->setEntityManager($entityManager);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setFichierHDRService($fichierHDRService);
        $controller->setInterventionService($interventionService);
        $controller->setJustificatifService($justificatifService);
        $controller->setMembreService($membreService);
        $controller->setParametreService($parametreService);

        $propositionTheseService = $container->get(PropositionTheseService::class);
        $controller->setPropositionTheseService($propositionTheseService);

        $propositionHDRService = $container->get(PropositionHDRService::class);
        $controller->setPropositionHDRService($propositionHDRService);

        return $controller;
    }
}