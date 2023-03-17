<?php

namespace Application\Controller\Factory;

use Application\Controller\DroitsController;
use Psr\Container\ContainerInterface;

class DroitsControllerFactory
{
    public function __invoke(ContainerInterface $container): DroitsController
    {
        return new DroitsController();
    }
}