<?php

namespace Doctorant\Service;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Rapport\RapportService;
use Application\Service\Source\SourceService;
use Doctrine\ORM\EntityManager;
use Individu\Service\IndividuService;
use RapportActivite\Rule\Creation\RapportActiviteCreationRule;
use RapportActivite\Service\RapportActiviteService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Structure\Service\Etablissement\EtablissementService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use These\Service\These\TheseService;

class DoctorantServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return DoctorantService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get('EtablissementService');

        $service = new DoctorantService();

        $service->setEtablissementService($etablissementService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($entityManager);

        /**
         * @var RapportService $rapportService
         */
        $rapportService = $container->get(RapportService::class);
        $service->setRapportService($rapportService);

        /**
         * @var RapportActiviteService $informationService
         */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $service->setRapportActiviteService($rapportActiviteService);

        /** @var \RapportActivite\Rule\Creation\RapportActiviteCreationRule $rapportActiviteCreationRule */
        $rapportActiviteCreationRule = $container->get(RapportActiviteCreationRule::class);
        $service->setRapportActiviteCreationRule($rapportActiviteCreationRule);

        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get(TheseService::class);
        $service->setTheseService($theseService);

        /**
         * @var PropositionTheseService $propositionService
         */
        $propositionService = $container->get(PropositionTheseService::class);
        $service->setPropositionTheseService($propositionService);

        /**
         * @var AnneeUnivService $anneeUnivService
         */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $service->setAnneeUnivService($anneeUnivService);

        return $service;
    }
}