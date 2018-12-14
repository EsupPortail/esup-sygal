<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseConsoleController;
use Application\Service\These\TheseService;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;
use Zend\Log\Writer\Stream;
use Zend\Mvc\Controller\ControllerManager;

class TheseConsoleControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return TheseConsoleController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var TheseService $theseService
         */
        $theseService = $sl->get('TheseService');

        $controller = new TheseConsoleController();
        $controller->setTheseService($theseService);
        $controller->setLogger($this->createLogger());

        return $controller;
    }

    /**
     * @return LoggerInterface
     */
    private function createLogger()
    {
        $logger = new Logger();
        $logger->addWriter(new Stream('php://output'));

        return $logger;
    }
}