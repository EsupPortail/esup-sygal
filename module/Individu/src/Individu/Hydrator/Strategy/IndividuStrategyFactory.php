<?php

namespace Individu\Hydrator\Strategy;

use Individu\Service\IndividuService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IndividuStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): IndividuStrategy
    {
        $strategy = new IndividuStrategy();

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $strategy->setIndividuService($individuService);

        return $strategy;
    }
}