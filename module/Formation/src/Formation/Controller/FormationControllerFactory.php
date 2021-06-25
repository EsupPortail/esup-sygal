<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Service\Formation\FormationService;
use Interop\Container\ContainerInterface;

class FormationControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var FormationService $formationService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $formationService = $container->get(FormationService::class);

        $controller = new FormationController();
        $controller->setEntityManager($entityManager);
        $controller->setFormationService($formationService);
        return $controller;
    }
}