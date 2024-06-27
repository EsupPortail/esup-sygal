<?php

namespace These\Fieldset\TitreAcces;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;

class TitreAccesHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TitreAccesHydrator
    {
        $hydrator = new TitreAccesHydrator();

        /** @var AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $hydrator->setAnneeUnivService($anneeUnivService);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $hydrator->setSourceService($sourceService);

        return $hydrator;
    }
}