<?php

namespace Doctorant\Service\Search;

use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;

class DoctorantSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantSearchService
    {
        $service = new DoctorantSearchService();
        
        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $service->setDoctorantService($doctorantService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $service->setStructureService($structureService);

        $service->setEtablissementTheseSearchFilter(new EtablissementSearchFilter());

        return $service;
    }
}