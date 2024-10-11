<?php

namespace Admission\Service\Exporter\Admission;

use Admission\Service\Transmission\TransmissionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionExporterFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : AdmissionExporter
    {
        /**
         * @var TransmissionService $transmissionService
         */
        $transmissionService = $container->get(TransmissionService::class);

        $exporter = new AdmissionExporter();
        $exporter->setTransmissionService($transmissionService);
        return $exporter;
    }
}