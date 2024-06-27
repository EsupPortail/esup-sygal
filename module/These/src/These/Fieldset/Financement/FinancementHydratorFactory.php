<?php

namespace These\Fieldset\Financement;

use Application\Service\Financement\FinancementService;
use Application\Service\Source\SourceService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class FinancementHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        $hydrator = new FinancementHydrator($entityManager);
//        $hydrator = new FinancementHydrator();

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $hydrator->setSourceService($sourceService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $hydrator->setDoctorantService($doctorantService);

        /** @var FinancementService $financementService */
        $financementService = $container->get(FinancementService::class);
        $hydrator->setFinancementService($financementService);

        return $hydrator;
    }
}