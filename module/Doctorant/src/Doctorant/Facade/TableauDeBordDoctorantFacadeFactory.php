<?php

namespace Doctorant\Facade;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\Rapport\RapportService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use RapportActivite\Rule\Creation\RapportActiviteCreationRule;
use RapportActivite\Service\RapportActiviteService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use These\Service\These\TheseService;

class TableauDeBordDoctorantFacadeFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TableauDeBordDoctorantFacade
     */
    public function __invoke(ContainerInterface $container): TableauDeBordDoctorantFacade
    {
        $facade = new TableauDeBordDoctorantFacade();

        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $facade->setEntityManager($entityManager);

        /**
         * @var RapportService $rapportService
         */
        $rapportService = $container->get(RapportService::class);
        $facade->setRapportService($rapportService);

        /**
         * @var RapportActiviteService $informationService
         */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $facade->setRapportActiviteService($rapportActiviteService);

        /** @var RapportActiviteCreationRule $rapportActiviteCreationRule */
        $rapportActiviteCreationRule = $container->get(RapportActiviteCreationRule::class);
        $facade->setRapportActiviteCreationRule($rapportActiviteCreationRule);

        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get(TheseService::class);
        $facade->setTheseService($theseService);

        /**
         * @var PropositionTheseService $propositionService
         */
        $propositionService = $container->get(PropositionTheseService::class);
        $facade->setPropositionTheseService($propositionService);

        /**
         * @var AnneeUnivService $anneeUnivService
         */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $facade->setAnneeUnivService($anneeUnivService);

        return $facade;
    }
}