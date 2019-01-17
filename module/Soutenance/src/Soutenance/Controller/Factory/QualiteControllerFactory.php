<?php

namespace Soutenance\Controller\Factory;

use Soutenance\Controller\QualiteController;
use Soutenance\Service\Membre\MembreService;
use Zend\Mvc\Controller\ControllerManager;

class QualiteControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return QualiteController
     */
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var MembreService $membreService
         */
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);

        /** @var QualiteController $controller */
        $controller = new QualiteController();
        $controller->setMembreService($membreService);

        return $controller;
    }
}