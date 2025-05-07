<?php

namespace SygalApiImpl\V1\Process;

use Application\Entity\Db\Source;
use Application\SourceCodeStringHelper;
use Psr\Container\ContainerInterface;
use UnicaenDbImport\Service\ImportService;
use UnicaenDbImport\Service\SynchroService;

class InscriptionAdministrativeImportProcessFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAdministrativeImportProcess
    {
        $service = new InscriptionAdministrativeImportProcess();

        /** @var \UnicaenDbImport\Service\ImportService $importService */
        $importService = $container->get(ImportService::class);
        $service->setImportService($importService);

        /** @var \UnicaenDbImport\Service\SynchroService $synchroService */
        $synchroService = $container->get(SynchroService::class);
        $service->setSynchroService($synchroService);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get('doctrine.connection.orm_default');
        $service->setDestinationConnection($connection);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        /** @var \Application\Entity\Db\Repository\SourceRepository $sourceRepository */
        $sourceRepository = $em->getRepository(Source::class);
        $service->setSourceRepository($sourceRepository);

        /** @var \Application\SourceCodeStringHelper $sourceCodeHelper */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
