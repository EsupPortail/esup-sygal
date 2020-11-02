<?php

namespace Import\Controller\Factory;

use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Import\Controller\SynchroController;
use Import\Service\SynchroService;
use Interop\Container\ContainerInterface;

class SynchroControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return SynchroController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService $theseService
         * @var NotifierService $notifierService
         */
        $theseService = $container->get('TheseService');
        $notifierService = $container->get(NotifierService::class);

        /** @var SynchroService $synchroService */
        $synchroService = $container->get(SynchroService::class);

        $controller = new SynchroController();
        $controller->setContainer($container);
        $controller->setSynchroService($synchroService);
        $controller->setTheseService($theseService);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}


