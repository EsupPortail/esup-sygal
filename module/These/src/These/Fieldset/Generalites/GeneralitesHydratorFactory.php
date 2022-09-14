<?php

namespace These\Fieldset\Generalites;

use Doctorant\Service\DoctorantService;
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
        $hydrator = new GeneralitesHydrator();

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $hydrator->setDoctorantService($doctorantService);

        return $hydrator;
    }
}