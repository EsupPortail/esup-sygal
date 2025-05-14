<?php

namespace Doctorant\Service;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class DoctorantServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return DoctorantService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get('EtablissementService');

        $service = new DoctorantService();

        $service->setEtablissementService($etablissementService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($entityManager);

        return $service;
    }
}