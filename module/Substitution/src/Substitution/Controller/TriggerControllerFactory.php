<?php

namespace Substitution\Controller;

use Psr\Container\ContainerInterface;
use Substitution\Service\Trigger\TriggerService;

class TriggerControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : TriggerController
    {
        $controller = new TriggerController();

        /** @var \Substitution\Service\Trigger\TriggerService $triggerService */
        $triggerService = $container->get(TriggerService::class);
        $controller->setTriggerService($triggerService);

        return $controller;
    }
}