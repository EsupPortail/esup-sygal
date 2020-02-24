<?php

namespace ComiteSuivi\Controller;

use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use Zend\Mvc\Controller\ControllerManager;

class IndexControllerFactory {

    /**
     * @param ControllerManager $manager
     * @return IndexController
     */
    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var ComiteSuiviService $comiteService
         * @var TheseService $theseService
         * @var UserContextService $contextService
         */
        $comiteService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');
        $contextService = $manager->getServiceLocator()->get('authUserContext');

        /** @var IndexController $controller */
        $controller = new IndexController();
        $controller->setComiteSuiviService($comiteService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($contextService);
        return $controller;
    }
}