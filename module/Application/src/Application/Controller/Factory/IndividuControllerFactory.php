<?php

namespace Application\Controller\Factory;

use Application\Controller\IndividuController;
use Application\Service\Individu\IndividuService;
use Interop\Container\ContainerInterface;

class IndividuControllerFactory {

    public function __invoke(ContainerInterface $container) : IndividuController
    {
        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get(IndividuService::class);

        $controller = new IndividuController();
        $controller->setIndividuService($individuService);
        return $controller;
    }
}