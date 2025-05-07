<?php

namespace InscriptionAdministrative\Listener;

use InscriptionAdministrative\Config\ModuleConfig;
use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcess;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InscriptionAdministrativeImportProcessListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): InscriptionAdministrativeImportProcessListener
    {
        $listener = new InscriptionAdministrativeImportProcessListener();

        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);

        /** @var InscriptionAdministrativeReceptionProcess $receptionProcess */
        $receptionProcess = $container->get(InscriptionAdministrativeReceptionProcess::class);
        $listener->setInscriptionAdministrativeReceptionProcess($receptionProcess);
        $listener->setRunReceptionProcessAfterImport($moduleConfig->getRunReceptionProcessAfterImport());

        return $listener;
    }
}