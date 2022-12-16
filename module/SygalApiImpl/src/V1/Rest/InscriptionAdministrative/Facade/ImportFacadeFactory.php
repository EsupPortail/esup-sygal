<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade;

use Psr\Container\ContainerInterface;
use UnicaenDbImport\Service\ImportService;
use UnicaenDbImport\Service\SynchroService;

class ImportFacadeFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ImportFacade
    {
        $service = new ImportFacade();

        /** @var \UnicaenDbImport\Service\ImportService $importService */
        $importService = $container->get(ImportService::class);
        $service->setImportService($importService);

        /** @var \UnicaenDbImport\Service\SynchroService $synchroService */
        $synchroService = $container->get(SynchroService::class);
        $service->setSynchroService($synchroService);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get('doctrine.connection.orm_default');
        $service->setDestinationConnection($connection);

        return $service;
    }
}
