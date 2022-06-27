<?php

namespace Formation\Controller;

use Individu\Service\IndividuService;
use Doctrine\ORM\EntityManager;
use Formation\Service\Formateur\FormateurService;
use Interop\Container\ContainerInterface;

class FormateurControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var FormateurService $formateurService
         * @var IndividuService $individuService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $formateurService = $container->get(FormateurService::class);
        $individuService = $container->get(IndividuService::class);

        $controller = new FormateurController();
        $controller->setEntityManager($entityManager);
        $controller->setFormateurService($formateurService);
        $controller->setIndividuService($individuService);
        return $controller;
    }
}