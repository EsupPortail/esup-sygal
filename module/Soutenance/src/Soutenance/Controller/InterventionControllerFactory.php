<?php

namespace Soutenance\Controller;

use Depot\Service\FichierThese\FichierTheseService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\These\TheseService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Intervention\InterventionService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
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
         * @var InterventionService $interventionService
         * @var JustificatifService $justificatifService
         * @var MembreService $membreService
         * @var ParametreService $parametreService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fichierTheseService = $container->get(FichierTheseService::class);
        $interventionService = $container->get(InterventionService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $membreService = $container->get(MembreService::class);
        $parametreService = $container->get(ParametreService::class);
        $propositionService = $container->get(PropositionService::class);
        $theseService = $container->get(TheseService::class);

        $controller = new InterventionController();
        $controller->setEntityManager($entityManager);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setInterventionService($interventionService);
        $controller->setJustificatifService($justificatifService);
        $controller->setMembreService($membreService);
        $controller->setParametreService($parametreService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);

        return $controller;
    }
}