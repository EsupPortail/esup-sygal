<?php

namespace Formation\Service\Inscription\Search;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\InscriptionRepositoryAwareTrait;

class InscriptionSearchService extends SearchService
{
    use InscriptionRepositoryAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_doctorant = 'doctorant';
    const NAME_liste = 'liste';

    public function init()
    {
        $libelleFilter = $this->createLibelleFilter();
        $doctorantFilter = $this->createDoctorantFilter();
        $listeFilter = $this->createListeFilter();

        $listeFilter->setData(Inscription::LISTES);

        $this->addFilters([
            $libelleFilter,
            $doctorantFilter,
            $listeFilter,
        ]);

        $this->addSorters([
            $this->createLibelleSorter()->setIsDefault(),
            $this->createDoctorantSorter(),
            $this->createListeSorter(),
        ]);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        // ATTENTION à bien sélectionner les relations utilisées par les filtres/tris et parcourues côté vue.
        return $this->inscriptionRepository->createQueryBuilder('i')
            ->addSelect('sess, doct, ind')
            ->join('i.session', 'sess')
            ->join('i.doctorant', 'doct')
            ->join('sess.formation', 'form')
            ->join('doct.individu', 'ind');
    }

    /********************************** FILTERS ****************************************/

    private function createDoctorantFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Nom doctorant", self::NAME_doctorant);
        $filter
            ->setWhereField('ind.nomUsuel')
            ->useLikeOperator();

        return $filter;
    }

    private function createListeFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("Liste", self::NAME_liste);
        $filter
            ->setAllowsNoneOption()
            ->setNoneOptionLabel("Aucune")
            ->setWhereField('i.liste');

        return $filter;
    }

    private function createLibelleFilter(): TextSearchFilter
    {
        $filter = new StrReducedTextSearchFilter("Libellé", self::NAME_libelle);
        $filter
            ->useLikeOperator()
            ->setWhereField('form.libelle');

        return $filter;
    }

    /********************************** SORTERS ****************************************/

    private function createLibelleSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Session", self::NAME_libelle);
        $sorter->setOrderByField('sess.libelle');

        return $sorter;
    }

    private function createDoctorantSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Modalité", self::NAME_liste);
        $sorter->setOrderByField("ind.nomUsuel");

        return $sorter;
    }

    private function createListeSorter(): SearchSorter
    {
        $sorter = new SearchSorter("Liste", self::NAME_libelle);
        $sorter->setOrderByField("i.liste");

        return $sorter;
    }
}