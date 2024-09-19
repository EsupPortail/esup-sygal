<?php

namespace InscriptionAdministrative\Service\Search;

use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Structure\Service\Structure\StructureService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class InscriptionAdministrativeSearchServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InscriptionAdministrativeSearchService
    {
        $service = new InscriptionAdministrativeSearchService();

        /** @var \InscriptionAdministrative\Service\InscriptionAdministrativeService $inscriptionAdministrativeService */
        $inscriptionAdministrativeService = $container->get(InscriptionAdministrativeService::class);
        $service->setInscriptionAdministrativeService($inscriptionAdministrativeService);

        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $service->setStructureService($structureService);

        /** @var TheseAnneeUnivService $theseAnneeUnivService */
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $service->setAnneesUnivs($theseAnneeUnivService);

        return $service;
    }
}