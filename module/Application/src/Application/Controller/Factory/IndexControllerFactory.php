<?php

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Application\Service\These\TheseService;
use Zend\Mvc\Controller\ControllerManager;

class IndexControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return IndexController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var TheseService $theseService
         */
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');

        $controller = new IndexController();
        $controller->setTheseService($theseService);

        return $controller;
    }
}
