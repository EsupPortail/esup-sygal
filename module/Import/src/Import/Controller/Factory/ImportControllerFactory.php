<?php

namespace Import\Controller\Factory;

use Structure\Service\Etablissement\EtablissementService;
use These\Service\These\TheseService;
use Application\SourceCodeStringHelper;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\ORM\EntityManager;
use Import\Controller\ImportController;
use Import\Service\ImportService;
use Interop\Container\ContainerInterface;
use UnicaenApp\Exception\RuntimeException;

class ImportControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return ImportController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /** @var ImportService $importService */
        $importService = $container->get(ImportService::class);

        /** @var TheseService $theseService */
        $theseService = $container->get('TheseService');

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get('EtablissementService');

        try {
            $config = $this->getConfig($container);
        } catch (AssertionFailedException $e) {
            throw new RuntimeException("Configuration invalide", null, $e);
        }

        $controller = new ImportController();
        $controller->setContainer($container);
        $controller->setEntityManager($entityManager);
        $controller->setImportService($importService);
        $controller->setTheseService($theseService);
        $controller->setEtablissementService($etablissementService);
        $controller->setConfig($config);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws AssertionFailedException
     */
    private function getConfig(ContainerInterface $container)
    {
        $config = $container->get('config');

        Assertion::keyIsset($config, 'import-api');

        return $config['import-api'];
    }
}