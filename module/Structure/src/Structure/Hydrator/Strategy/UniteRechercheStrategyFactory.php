<?php

namespace Structure\Hydrator\Strategy;

use Structure\Service\UniteRecherche\UniteRechercheService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UniteRechercheStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): UniteRechercheStrategy
    {
        $strategy = new UniteRechercheStrategy();

        /** @var \Structure\Service\UniteRecherche\UniteRechercheService $etablissementService */
        $etablissementService = $container->get(UniteRechercheService::class);
        $strategy->setUniteRechercheService($etablissementService);

        return $strategy;
    }
}
