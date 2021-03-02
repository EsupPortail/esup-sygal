<?php

namespace ComiteSuivi\Controller;

use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use Interop\Container\ContainerInterface;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ComiteSuiviService $comiteService
         * @var TheseService $theseService
         * @var UserContextService $contextService
         */
        $comiteService = $container->get(ComiteSuiviService::class);
        $theseService = $container->get('TheseService');
        $contextService = $container->get('authUserContext');

        $controller = new IndexController();
        $controller->setComiteSuiviService($comiteService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($contextService);
        return $controller;
    }
}