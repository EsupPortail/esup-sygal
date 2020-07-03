<?php


namespace Application\Search\Filter\Provider;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Financement\FinancementService;
use Application\Service\RapportAnnuel\RapportAnnuelService;
use Application\Service\Structure\StructureService;
use Application\Service\These\TheseRechercheService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class SearchFilterProviderServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var StructureService $structureService
         * @var EtablissementService $etablissementService
         * @var FinancementService $financementService
         * @var TheseAnneeUnivService $theseAnneeUnivService
         * @var TheseRechercheService $theseRechercheService
         * @var RapportAnnuelService $rapportAnnuelService
         */
        $structureService = $container->get(StructureService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $theseRechercheService = $container->get(TheseRechercheService::class);
        $rapportAnnuelService = $container->get(RapportAnnuelService::class);

        $service = new SearchFilterProviderService();
        $service->setFinancementService($financementService);
        $service->setTheseAnneeUnivService($theseAnneeUnivService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setTheseRechercheService($theseRechercheService);
        $service->setRapportAnnuelService($rapportAnnuelService);

        return $service;
    }
}