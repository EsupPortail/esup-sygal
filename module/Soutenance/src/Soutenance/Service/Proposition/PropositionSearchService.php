<?php

namespace Soutenance\Service\Proposition;

use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\TypeStructure;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilterAwareTrait;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;

class PropositionSearchService extends SearchService
{
    use PropositionServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;

    use EtablissementInscSearchFilterAwareTrait;
    use UniteRechercheSearchFilterAwareTrait;
    use EcoleDoctoraleSearchFilterAwareTrait;

    const NAME_ETAT = 'etat';

    /**
     * @inheritDoc
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->propositionService->getRepository()->createQueryBuilder('proposition');
        $qb
            ->addSelect('etat')->join('proposition.etat', 'etat')
            ->addSelect('these')->join('proposition.these', 'these')
            ->addSelect('ur')->leftJoin('these.uniteRecherche', 'ur')
            ->addSelect('ed')->leftJoin('these.ecoleDoctorale', 'ed')
            ->addSelect('etab')->leftJoin('these.etablissement', 'etab')
            ->addSelect('membre')->leftJoin('proposition.membres', 'membre')
            ->addSelect('qualite')->leftJoin('membre.qualite', 'qualite')
            ->addSelect('acteur')->leftJoin('membre.acteur', 'acteur')
            ->addSelect('justificatif')->leftJoin('proposition.justificatifs', 'justificatif')
            ->addSelect('avis')->leftJoin('proposition.avis', 'avis')
            ->andWhere('proposition.histoDestruction is null')
            ->andWhere('these.histoDestruction is null')
            ->andWhere('proposition.date is not null')
            ->andWhere('these.etatThese = :etatThese')->setParameter('etatThese', These::ETAT_EN_COURS)
            //->addSelect('validation')->leftJoin('proposition.validations', 'validation')
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
                    ->andWhere($qb->expr()->orX('etab.sourceCode = :sourceCodeEtab', 'etab_substituant.sourceCode = :sourceCodeEtab'))
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });

        $ecoleDoctoraleFilter = $this->getEcoleDoctoraleSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchEcolesDoctorales($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                $qb
                    ->andWhere($qb->expr()->orX('ed.sourceCode = :sourceCodeED', 'ed_substituant.sourceCode = :sourceCodeED'))
                    ->setParameter('sourceCodeED', $filter->getValue());
            });

        $uniteRechercheFilter = $this->getUniteRechercheSearchFilter()
            ->setDataProvider(function(SelectSearchFilter $filter) {
                return $this->fetchUnitesRecherches($filter);
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'these') {
                $qb
                    ->andWhere($qb->expr()->orX('ur.sourceCode = :sourceCodeUR', 'ur_substituant.sourceCode = :sourceCodeUR'))
                    ->setParameter('sourceCodeUR', $filter->getValue());
            });

        $etatFilter = new SelectSearchFilter("État", self::NAME_ETAT);
        $etatFilter
            ->setDataProvider(fn() => $this->propositionService->findPropositionEtats())
            ->setWhereField('etat.code');

        $this->addFilters([
            $etablissementInscrFilter,
            $ecoleDoctoraleFilter,
            $uniteRechercheFilter,
            $etatFilter,
        ]);
        $this->addSorters([
            $this->createSorterEtablissement(),
            $this->createSorterEcoleDoctorale(),
            $this->createSorterUniteRecherche(),
        ]);
    }


    ////////////////////////////////// Fetch /////////////////////////////////////

    private function fetchEtablissements(SelectSearchFilter $filter): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }

    private function fetchEcolesDoctorales(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_ECOLE_DOCTORALE, 'sigle', true);
    }

    private function fetchUnitesRecherches(SelectSearchFilter $filter): array
    {
        return $this->structureService->findAllStructuresAffichablesByType(
            TypeStructure::CODE_UNITE_RECHERCHE, 'code', true);
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

    public function createSorterEcoleDoctorale(): SearchSorter
    {
        $sorter = new SearchSorter("École doctorale", EcoleDoctoraleSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, DefaultQueryBuilder $qb) {
                $qb->addOrderBy('ed_structure.sigle', $sorter->getDirection());
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
