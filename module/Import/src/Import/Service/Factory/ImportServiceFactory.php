<?php

namespace Import\Service\Factory;

use Doctrine\ORM\EntityManager;
use Import\Service\ImportService;
use Import\Service\SynchroService;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Noop;
use Structure\Service\Etablissement\EtablissementService;

class ImportServiceFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var SynchroService $synchroService */
        $synchroService = $container->get(SynchroService::class);

        /** @var EtablissementService $etbalissementService */
        $etbalissementService = $container->get(EtablissementService::class);

        $logger = (new Logger())->addWriter(new Noop());

        $service = new ImportService();
        $service->setEntityManager($entityManager);
        $service->setSynchroService($synchroService);
        $service->setEtablissementService($etbalissementService);
        $service->setLogger($logger);

        return $service;
    }
}