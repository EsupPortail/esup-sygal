<?php

namespace Formation\Service\Formation\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Repository\FormationRepositoryAwareTrait;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class FormationSearchService extends SearchService
{
    use FormationRepositoryAwareTrait;

    use EtablissementServiceAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_modalite = 'modalite';
    const NAME_responsable = 'responsable';
    const NAME_site = 'site';
    const NAME_structure = 'structure';

    public function init()
    {
        $siteFilter = $this->createSiteFilter();
        $structureFilter = $this->createStructureFilter();
        $libelleFilter = $this->createLibelleFilter();
        $responsableFilter = $this->createResponsableFilter();
        $modaliteFilter = $this->createModaliteFilter();

        $siteFilter->setDataProvider(fn() => $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true));
        $structureFilter->setDataProvider(fn() => $this->formationRepository->fetchListeStructures());
        $responsableFilter->setDataProvider(fn() => $this->formationRepository->fetchListeResponsable());
        $modaliteFilter->setData(HasModaliteInterface::MODALITES);

        $this->addFilters([
            $siteFilter,
            $responsableFilter,
            $structureFilter,
            $libelleFilter,
            $modaliteFilter,
        ]);

        $this->addSorters([
            $this->createLibelleSorter()->setIsDefault(),
            $this->createSiteSorter(),
            $this->createStructureSorter(),
            $this->createResponsableSorter(),
            $this->createModaliteSorter(),
        ]);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        return $this->formationRepository->createQueryBuilder('f')
            ->addSelect('resp, site, struct')
            ->leftJoin("f.responsable", 'resp')
            ->leftJoin("f.site", 'site')
            ->leftJoin("f.typeStructure", 'struct');
    }

    /********************************** FILTERS ****************************************/

    public function createSiteFilter(): EtablissementSearchFilter
    {
        return EtablissementSearchFilter::newInstance()
            ->setName(self::NAME_site)
            ->setLabel("Site")
            ->setWhereField('site.sourceCode'); // cf. `join("f.site", 'site')` fait dans `createQueryBuilder()`
    }

    private function createStructureFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Structure associée", self::NAME_structure);
        $filter->setQueryBuilderApplier(
            function(SelectSearchFilter $filter, QueryBuilder $qb) {
                $qb
                    ->andWhere("struct = :structure")
                    ->setParameter('structure', $filter->getValue());
            }
        );

        return $filter;
    }

    private function createResponsableFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Responsable", self::NAME_responsable);
        $filter->setQueryBuilderApplier(
            function(SelectSearchFilter $filter, QueryBuilder $qb) {
                $qb
                    ->andWhere("resp = :responsable")
                    ->setParameter('responsable', $filter->getValue());
            }
        );

        return $filter;
    }

    private function createModaliteFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Modalité", self::NAME_modalite);
        $filter->setWhereField('f.modalite');

        return $filter;
    }

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new TextSearchFilter("Libellé", self::NAME_libelle);
        $filter->setQueryBuilderApplier([$this, 'applyLibelleFilter']);

        return $filter;
    }

    public function applyLibelleFilter(SearchFilter $filter, QueryBuilder $qb, string $alias = 'f')
    {
        $filterValue = $filter->getValue();

        if ($filterValue !== null && strlen($filterValue) > 1) {
            $qb
                ->andWhere("strReduce($alias.libelle) LIKE strReduce(:text)")
                ->setParameter('text', '%' . $filterValue . '%');
        }
    }

    /********************************** SORTERS ****************************************/

    /**
     * @return SearchSorter
     */
    public function createSiteSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Site", EtablissementSearchFilter::NAME);
        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb) {
                $qb
                    ->join('site.structure', 's_sort')
                    ->addOrderBy('s_sort.code', $sorter->getDirection());
            }
        );

        return $sorter;
    }

    private function createStructureSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Structure associée", self::NAME_structure);
        $sorter->setQueryBuilderApplier([$this, 'applyStructureSorter']);

        return $sorter;
    }

    public function applyStructureSorter(SearchSorter $sorter, QueryBuilder $qb, string $alias = 'f')
    {
        $qb->addOrderBy("struct.libelle", $sorter->getDirection());
    }

    private function createResponsableSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Responsable", self::NAME_responsable);
        $sorter->setOrderByField("resp.nomUsuel");

        return $sorter;
    }

    private function createModaliteSorter(): SearchSorter
    {
        return new SearchSorter("Modalité", self::NAME_modalite);
        // $sorter->setOrderByField() inutile car self::NAME_modalite === nom de l'attribut d'entité.
    }

    private function createLibelleSorter(): SearchSorter
    {
        return new SearchSorter("Libellé", self::NAME_libelle);
        // $sorter->setOrderByField() inutile car self::NAME_libelle === nom de l'attribut d'entité.
    }
}