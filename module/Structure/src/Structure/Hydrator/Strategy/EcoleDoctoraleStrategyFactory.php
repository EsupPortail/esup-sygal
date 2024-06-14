<?php

namespace Structure\Hydrator\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;

class EcoleDoctoraleStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EcoleDoctoraleStrategy
    {
        $strategy = new EcoleDoctoraleStrategy();

        /** @var \Structure\Service\EcoleDoctorale\EcoleDoctoraleService $etablissementService */
        $etablissementService = $container->get(EcoleDoctoraleService::class);
        $strategy->setEcoleDoctoraleService($etablissementService);

        return $strategy;
    }
}