<?php

namespace InscriptionAdministrative\Listener;

use InscriptionAdministrative\Process\Reception\ReceptionProcess;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ImportProcessListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ImportProcessListener
    {
        $service = new ImportProcessListener();

        /** @var ReceptionProcess $receptionProcess */
        $receptionProcess = $container->get(ReceptionProcess::class);
        $service->setReceptionProcess($receptionProcess);

        return $service;
    }
}