<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Process;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Psr\Container\ContainerInterface;
use UnicaenDbImport\Service\ImportService;
use UnicaenDbImport\Service\SynchroService;

class ImportProcessFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ImportProcess
    {
        $service = new ImportProcess();

        /** @var \UnicaenDbImport\Service\ImportService $importService */
        $importService = $container->get(ImportService::class);
        $service->setImportService($importService);

        /** @var \UnicaenDbImport\Service\SynchroService $synchroService */
        $synchroService = $container->get(SynchroService::class);
        $service->setSynchroService($synchroService);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get('doctrine.connection.orm_default');
        $service->setDestinationConnection($connection);

        /** @var \Application\Service\Source\SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        /** @var \Application\SourceCodeStringHelper $sourceCodeHelper */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
