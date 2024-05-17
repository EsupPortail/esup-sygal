<?php

namespace Formation\Service\Module\Search;

use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Repository\ModuleRepositoryAwareTrait;

class ModuleSearchService extends SearchService
{
    use ModuleRepositoryAwareTrait;

    const NAME_libelle = 'libelle';

    public function init()
    {
        $this->addFilters([
            $this->createLibelleFilter(),
        ]);

        $this->addSorters([
            $this->createLibelleSorter()->setIsDefault(),
        ]);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        return $this->moduleRepository->createQueryBuilder('m');
    }

    /********************************** FILTERS ****************************************/

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé", self::NAME_libelle);
        $filter
            ->useLikeOperator()
            ->setWhereField('m.libelle');

        return $filter;
    }

    /********************************** SORTERS ****************************************/

    private function createLibelleSorter(): SearchSorter
    {
        return new SearchSorter("Libellé", self::NAME_libelle);
        // $sorter->setOrderByField() inutile car self::NAME_libelle === nom de l'attribut d'entité.
    }
}