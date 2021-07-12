<?php

namespace Application\Service\Rapport;

use Application\Entity\Db\TypeValidation;
use Application\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Application\Search\Etablissement\EtablissementSearchFilter;
use Application\Search\Rapport\AnneeRapportActiviteSearchFilter;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Financement\FinancementService;
use Application\Search\Financement\OrigineFinancementSearchFilter;
use Application\Service\Structure\StructureService;
use Application\Service\These\TheseSearchService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Search\UniteRecherche\UniteRechercheSearchFilter;
use Application\Service\Validation\ValidationService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
         * @var TheseSearchService $theseSearchService
         * @var RapportService $rapportService
         * @var ValidationService $validationService
         */
        $structureService = $container->get(StructureService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $theseSearchService = $container->get(TheseSearchService::class);
        $rapportService = $container->get(RapportService::class);
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE);

        $service->setFinancementService($financementService);
        $service->setTheseAnneeUnivService($theseAnneeUnivService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setTheseSearchService($theseSearchService);
        $service->setRapportService($rapportService);
        $service->setTypeValidation($typeValidation);

        $service->setEtablissementTheseSearchFilter(EtablissementSearchFilter::newInstance());
        $service->setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter::newInstance());
        $service->setUniteRechercheSearchFilter(UniteRechercheSearchFilter::newInstance());
        $service->setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter::newInstance());
        $service->setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter::newInstance());

        return $service;
    }
}