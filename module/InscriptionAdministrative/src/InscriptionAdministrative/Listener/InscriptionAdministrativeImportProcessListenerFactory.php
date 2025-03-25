<?php

namespace InscriptionAdministrative\Listener;

use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcess;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InscriptionAdministrativeImportProcessListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): InscriptionAdministrativeImportProcessListener
    {
        $service = new InscriptionAdministrativeImportProcessListener();

        /** @var InscriptionAdministrativeReceptionProcess $receptionProcess */
        $receptionProcess = $container->get(InscriptionAdministrativeReceptionProcess::class);
        $service->setInscriptionAdministrativeReceptionProcess($receptionProcess);

        $config = $container->get('config')['sygal']['inscription_administrative'] ?? [];
        $service->setRunReceptionProcessAfterImportProcess($config['run_reception_process_after_import_process'] ?? false);

        return $service;
    }
}