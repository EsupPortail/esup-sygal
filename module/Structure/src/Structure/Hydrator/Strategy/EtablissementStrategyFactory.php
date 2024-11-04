<?php

namespace Structure\Hydrator\Strategy;

use Structure\Service\Etablissement\EtablissementService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EtablissementStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EtablissementStrategy
    {
        $strategy = new EtablissementStrategy();

        /** @var \Structure\Service\Etablissement\EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $strategy->setEtablissementService($etablissementService);

        return $strategy;
    }
}
