<?php

namespace Application\Service\Rapport;

use Application\Service\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Application\Service\Etablissement\EtablissementInscSearchFilter;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Financement\FinancementService;
use Application\Service\Financement\OrigineFinancementSearchFilter;
use Application\Service\Structure\StructureService;
use Application\Service\These\TheseRechercheService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\UniteRecherche\UniteRechercheSearchFilter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RapportSearchServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RapportSearchService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RapportSearchService
    {
        /**
         * @var RapportService $rapportService
         */
        $rapportService = $container->get(RapportService::class);

        $service = new RapportSearchService();
        $service->setRapportService($rapportService);

        /**
         * @var StructureService $structureService
         * @var EtablissementService $etablissementService
         * @var FinancementService $financementService
         * @var TheseAnneeUnivService $theseAnneeUnivService
         * @var TheseRechercheService $theseRechercheService
         * @var RapportService $rapportService
         */
        $structureService = $container->get(StructureService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $theseRechercheService = $container->get(TheseRechercheService::class);
        $rapportService = $container->get(RapportService::class);

        $service->setFinancementService($financementService);
        $service->setTheseAnneeUnivService($theseAnneeUnivService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setTheseRechercheService($theseRechercheService);
        $service->setRapportService($rapportService);

        $service->setEtablissementInscSearchFilter(EtablissementInscSearchFilter::newInstance());
        $service->setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter::newInstance());
        $service->setUniteRechercheSearchFilter(UniteRechercheSearchFilter::newInstance());
        $service->setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter::newInstance());
        $service->setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter::newInstance());

        return $service;
    }
}