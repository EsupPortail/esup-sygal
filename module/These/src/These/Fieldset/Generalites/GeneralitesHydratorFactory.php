<?php

namespace These\Fieldset\Generalites;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Source\SourceService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class GeneralitesHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GeneralitesHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        $hydrator = new GeneralitesHydrator($entityManager);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $hydrator->setDoctorantService($doctorantService);

        /** @var AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $hydrator->setAnneeUnivService($anneeUnivService);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $hydrator->setSourceService($sourceService);

        return $hydrator;
    }
}