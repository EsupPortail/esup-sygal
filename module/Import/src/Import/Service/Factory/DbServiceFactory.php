<?php

namespace Import\Service\Factory;

use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use Import\Service\DbService;
use Import\Service\DbServiceJSONHelper;
use Import\Service\DbServiceSQLGenerator;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class DbServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);

        $dbServiceJSONHelper = new DbServiceJSONHelper();
        $dbServiceJSONHelper->setSourceCodeStringHelper($sourceCodeHelper);

        $service = new DbService();
        $service->setEntityManager($entityManager);
        $service->setSqlGenerator(new DbServiceSQLGenerator());
        $service->setJsonHelper($dbServiceJSONHelper);

        return $service;
    }
}