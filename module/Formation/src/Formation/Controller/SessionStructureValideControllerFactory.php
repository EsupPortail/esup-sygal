<?php

namespace Formation\Controller;

use Formation\Entity\Db\SessionStructureValide;
use Formation\Form\SessionStructureValide\SessionStructureValideForm;
use Formation\Form\SessionStructureValide\SessionStructureValideFormAwareTrait;
use Formation\Service\Session\SessionService;
use Formation\Service\Session\SessionServiceAwareTrait;
use Formation\Service\SessionStructureValide\SessionStructureValideService;
use Formation\Service\SessionStructureValide\SessionStructureValideServiceAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionStructureValideControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureValideController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureValideController
    {
        /**
         * @var SessionService $sessionService
         * @var SessionStructureValideService $sessionStructureComplementaireService
         */
        $sessionService = $container->get(SessionService::class);
        $sessionStructureComplementaireService = $container->get(SessionStructureValideService::class);

        /**
         * @var SessionStructureValideForm $sessionStructureComplementaireForm
         */
        $sessionStructureComplementaireForm = $container->get('FormElementManager')->get(SessionStructureValideForm::class);

        $controller = new SessionStructureValideController();
        $controller->setSessionService($sessionService);
        $controller->setSessionStructureValideService($sessionStructureComplementaireService);
        $controller->setSessionStructureValideForm($sessionStructureComplementaireForm);
        return $controller;
    }
}