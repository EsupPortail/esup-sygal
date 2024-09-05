<?php

namespace These\Service\Exporter\CoEncadrements;

use Application\Service\Role\RoleService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenRenderer\Service\Rendu\RenduService;

class CoEncadrementsExporterFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : CoEncadrementsExporter
    {
        /**
         * @var RenduService $renduService
         */
        $renduService = $container->get(RenduService::class);
        $roleService = $container->get(RoleService::class);
        $renderer = $container->get('ViewRenderer');

        $exporter = new CoEncadrementsExporter($renderer, 'A4');
        $exporter->setRenduService($renduService);
        $exporter->setRoleService($roleService);
        return $exporter;
    }
}