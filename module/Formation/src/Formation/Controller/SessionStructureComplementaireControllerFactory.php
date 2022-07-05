<?php

namespace Formation\Controller;

use Formation\Entity\Db\SessionStructureComplementaire;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireForm;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireFormAwareTrait;
use Formation\Service\Session\SessionService;
use Formation\Service\Session\SessionServiceAwareTrait;
use Formation\Service\SessionStructureComplementaire\SessionStructureComplementaireService;
use Formation\Service\SessionStructureComplementaire\SessionStructureComplementaireServiceAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionStructureComplementaireControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureComplementaireController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureComplementaireController
    {
        /**
         * @var SessionService $sessionService
         * @var SessionStructureComplementaireService $sessionStructureComplementaireService
         */
        $sessionService = $container->get(SessionService::class);
        $sessionStructureComplementaireService = $container->get(SessionStructureComplementaireService::class);

        /**
         * @var SessionStructureComplementaireForm $sessionStructureComplementaireForm
         */
        $sessionStructureComplementaireForm = $container->get('FormElementManager')->get(SessionStructureComplementaireForm::class);

        $controller = new SessionStructureComplementaireController();
        $controller->setSessionService($sessionService);
        $controller->setSessionStructureComplementaireService($sessionStructureComplementaireService);
        $controller->setSessionStructureComplementaireForm($sessionStructureComplementaireForm);
        return $controller;
    }
}