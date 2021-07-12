<?php

namespace Import\Service\Factory;

use Application\Service\Etablissement\EtablissementService;
use Doctrine\ORM\EntityManager;
use Import\Service\FetcherService;
use Import\Service\ImportService;
use Import\Service\SynchroService;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Noop;
use Interop\Container\ContainerInterface;

class ImportServiceFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var SynchroService $synchroService */
        $synchroService = $container->get(SynchroService::class);

        /** @var FetcherService $fetcherService */
        $fetcherService = $container->get(FetcherService::class);

        /** @var EtablissementService $etbalissementService */
        $etbalissementService = $container->get(EtablissementService::class);

        $logger = (new Logger())->addWriter(new Noop());

        $service = new ImportService();
        $service->setFetcherService($fetcherService);
        $service->setEntityManager($entityManager);
        $service->setSynchroService($synchroService);
        $service->setEtablissementService($etbalissementService);
        $service->setLogger($logger);

        return $service;
    }
}