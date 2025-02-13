<?php

namespace Application\Service\Rapport;

use Application\Search\Financement\OrigineFinancementSearchFilter;
use Application\Service\Financement\FinancementService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RapportActivite\Search\AnneeRapportActiviteSearchFilter;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use These\Service\These\TheseSearchService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Validation\Entity\Db\TypeValidation;
use Validation\Service\ValidationService;

class RapportSearchServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RapportSearchService
    {
        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO);

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
         */
        $structureService = $container->get(StructureService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $theseSearchService = $container->get(TheseSearchService::class);
        $rapportService = $container->get(RapportService::class);

        $service->setFinancementService($financementService);
        $service->setAnneesUnivs($theseAnneeUnivService);
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