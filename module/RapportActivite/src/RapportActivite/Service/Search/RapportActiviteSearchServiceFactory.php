<?php

namespace RapportActivite\Service\Search;

use Application\Entity\Db\TypeValidation;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Application\Search\Financement\OrigineFinancementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Financement\FinancementService;
use Structure\Service\Structure\StructureService;
use These\Service\These\TheseSearchService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\Validation\ValidationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RapportActivite\Search\AnneeRapportActiviteSearchFilter;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteSearchServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RapportActiviteSearchService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RapportActiviteSearchService
    {
        /**
         * @var RapportActiviteService $rapportActiviteService
         */
        $rapportActiviteService = $container->get(RapportActiviteService::class);

        $service = new RapportActiviteSearchService();
        $service->setRapportActiviteService($rapportActiviteService);

        /**
         * @var StructureService $structureService
         * @var EtablissementService $etablissementService
         * @var FinancementService $financementService
         * @var TheseAnneeUnivService $theseAnneeUnivService
         * @var TheseSearchService $theseSearchService
         * @var RapportActiviteService $rapportActiviteService
         * @var RapportActiviteAvisService $rapportActiviteAvisService
         * @var ValidationService $validationService
         */
        $structureService = $container->get(StructureService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $theseSearchService = $container->get(TheseSearchService::class);
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE);

        $service->setFinancementService($financementService);
        $service->setAnneesUnivs($theseAnneeUnivService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setTheseSearchService($theseSearchService);
        $service->setRapportActiviteService($rapportActiviteService);
        $service->setRapportActiviteAvisService($rapportActiviteAvisService);
        $service->setTypeValidation($typeValidation);

        $service->setEtablissementTheseSearchFilter(EtablissementSearchFilter::newInstance());
        $service->setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter::newInstance());
        $service->setUniteRechercheSearchFilter(UniteRechercheSearchFilter::newInstance());
        $service->setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter::newInstance());
        $service->setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter::newInstance());

        return $service;
    }
}