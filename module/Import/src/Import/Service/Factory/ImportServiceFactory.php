<?php

namespace Import\Service\Factory;

use Application\Service\Etablissement\EtablissementService;
use Doctrine\ORM\EntityManager;
use Import\Service\FetcherService;
use Import\Service\ImportService;
use Import\Service\SynchroService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

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

        $service = new ImportService();
        $service->setFetcherService($fetcherService);
        $service->setEntityManager($entityManager);
        $service->setSynchroService($synchroService);
        $service->setEtablissementService($etbalissementService);

        return $service;
    }
}