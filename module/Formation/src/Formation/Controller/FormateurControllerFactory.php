<?php

namespace Formation\Controller;

use Formation\Service\Session\SessionService;
use Individu\Service\IndividuService;
use Doctrine\ORM\EntityManager;
use Formation\Service\Formateur\FormateurService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormateurControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return FormateurController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : FormateurController
    {
        /**
         * @var EntityManager $entityManager
         * @var FormateurService $formateurService
         * @var IndividuService $individuService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $formateurService = $container->get(FormateurService::class);
        $individuService = $container->get(IndividuService::class);
        $sessionService = $container->get(SessionService::class);

        $controller = new FormateurController();
        $controller->setEntityManager($entityManager);
        $controller->setFormateurService($formateurService);
        $controller->setIndividuService($individuService);
        $controller->setSessionService($sessionService);
        return $controller;
    }
}