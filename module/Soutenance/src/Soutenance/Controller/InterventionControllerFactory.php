<?php

namespace Soutenance\Controller;

use Application\Service\These\TheseService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Intervention\InterventionService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;

class InterventionControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return InterventionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var InterventionService $interventionService
         * @var JustificatifService $justificatifService
         * @var ParametreService $parametreService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $interventionService = $container->get(InterventionService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $parametreService = $container->get(ParametreService::class);
        $propositionService = $container->get(PropositionService::class);
        $theseService = $container->get(TheseService::class);

        $controller = new InterventionController();
        $controller->setEntityManager($entityManager);
        $controller->setInterventionService($interventionService);
        $controller->setJustificatifService($justificatifService);
        $controller->setParametreService($parametreService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);

        return $controller;
    }
}