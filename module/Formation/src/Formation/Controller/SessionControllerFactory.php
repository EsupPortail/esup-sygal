<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Form\Seance\SeanceForm;
use Formation\Form\Session\SessionForm;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Seance\SeanceService;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;

class SessionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var InscriptionService $inscriptionService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $inscriptionService = $container->get(InscriptionService::class);
        $sessionService = $container->get(SessionService::class);

        /**
         * @var SessionForm $sessionForm
         */
        $sessionForm = $container->get('FormElementManager')->get(SessionForm::class);

        $controller = new SessionController();
        $controller->setEntityManager($entityManager);
        $controller->setInscriptionService($inscriptionService);
        $controller->setSessionService($sessionService);
        $controller->setSessionForm($sessionForm);

        return $controller;
    }
}