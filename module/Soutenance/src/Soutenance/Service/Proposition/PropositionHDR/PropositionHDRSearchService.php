<?php

namespace Soutenance\Service\Proposition\PropositionHDR;

use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use HDR\Entity\Db\HDR;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilterAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;

class PropositionHDRSearchService extends SearchService
{
    use PropositionHDRServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    use EtablissementInscSearchFilterAwareTrait;
    use UniteRechercheSearchFilterAwareTrait;

    const NAME_ETAT = 'etat';

    /**
     * @inheritDoc
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->propositionHDRService->getRepository()->createQueryBuilder('proposition');
        $qb
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('hdr')->join('proposition.hdr', 'hdr')
            ->addSelect('unite')->leftJoin('hdr.uniteRecherche', 'unite')
            ->addSelect('ur')->leftJoin('unite.structure', 'ur')
            ->addSelect('ecole')->leftJoin('hdr.ecoleDoctorale', 'ecole')
            ->addSelect('structure_ed')->leftJoin('ecole.structure', 'structure_ed')
            ->addSelect('etab')->leftJoin('hdr.etablissement', 'etab')
            ->addSelect('structure_etab')->leftJoin('etab.structure', 'structure_etab')
            ->addSelect('membre')->leftJoin('proposition.membres', 'membre')
            ->addSelect('qualite')->leftJoin('membre.qualite', 'qualite')
//            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur') // n'existe plus
//            ->addSelect('amembre')->leftJoin('acteur.membre', 'amembre')
            ->addSelect('justificatif')->leftJoin('proposition.justificatifs', 'justificatif')
            ->addSelect('avis')->leftJoin('proposition.avis', 'avis')
            ->andWhere('proposition.histoDestruction is null')
            ->andWhere('hdr.histoDestruction is null')
            ->andWhere('proposition.date is not null')
            ->andWhere('hdr.etatHDR = :etatHDR')->setParameter('etatHDR', HDR::ETAT_EN_COURS)
            ->orderBy('proposition.date', 'ASC')
        ;

        return $qb;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEtablissements($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                $qb
                    ->andWhere('etab.sourceCode = :sourceCodeEtab')
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });

        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchUnitesRecherches($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                $qb
                    ->andWhere('ur.sourceCode = :sourceCodeUR')
                    ->setParameter('sourceCodeUR', $filter->getValue());
            });

        $etatFilter = new SelectSearchFilter("État", self::NAME_ETAT);
        $etatFilter
            ->setDataProvider(fn() => $this->propositionHDRService->findPropositionEtats())
            ->setWhereField('etat.code');

        $this->addFilters([
            $etablissementInscrFilter,
            $uniteRechercheFilter,
            $etatFilter,
        ]);
        $this->addSorters([
            $this->createSorterEtablissement(),
            $this->createSorterUniteRecherche(),
        ]);
    }


    ////////////////////////////////// Fetch /////////////////////////////////////

    private function fetchEtablissements(SelectSearchFilter $filter): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchUnitesRecherches(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE, 'structure.code');
    }

    /////////////////////////////////////// Sorters /////////////////////////////////////////

    /**
     * @return SearchSorter
     */
    public function createSorterEtablissement(): SearchSorter
    {
        $sorter = new SearchSorter("Établissement<br>d'inscr.", EtablissementSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('etab_structure.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    public function createSorterUniteRecherche(): SearchSorter
    {
        $sorter = new SearchSorter("Unité recherche", UniteRechercheSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('ur_structure.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }
}
