<?php

namespace RapportActivite\Service\Search;

use Application\Search\Financement\OrigineFinancementSearchFilter;
use Application\Service\Financement\FinancementService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Search\AnneeRapportActiviteSearchFilter;
use RapportActivite\Service\Operation\RapportActiviteOperationService;
use RapportActivite\Service\RapportActiviteService;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use These\Service\These\TheseSearchService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Validation\Entity\Db\TypeValidation;
use Validation\Service\ValidationThese\ValidationTheseService;
use Validation\Service\ValidationService;

class RapportActiviteSearchServiceFactory implements FactoryInterface
{
    /**
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
         * @var ValidationTheseService $validationService
         */
        $structureService = $container->get(StructureService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $theseSearchService = $container->get(TheseSearchService::class);
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $validationService = $container->get(ValidationTheseService::class);

        $service->setFinancementService($financementService);
        $service->setAnneesUnivs($theseAnneeUnivService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setTheseSearchService($theseSearchService);
        $service->setRapportActiviteService($rapportActiviteService);

        $service->setEtablissementTheseSearchFilter(EtablissementSearchFilter::newInstance());
        $service->setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter::newInstance());
        $service->setUniteRechercheSearchFilter(UniteRechercheSearchFilter::newInstance());
        $service->setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter::newInstance());
        $service->setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter::newInstance());

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $service->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        /** @var \RapportActivite\Service\Operation\RapportActiviteOperationService $rapportActiviteOperationService */
        $rapportActiviteOperationService = $container->get(RapportActiviteOperationService::class);
        $service->setRapportActiviteOperationService($rapportActiviteOperationService);

        /** @var \Validation\Service\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO);
        $service->setTypeValidation($typeValidation);

        return $service;
    }
}