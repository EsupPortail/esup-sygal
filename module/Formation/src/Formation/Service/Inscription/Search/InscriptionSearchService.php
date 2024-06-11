<?php

namespace Formation\Service\Inscription\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\StrReducedTextSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\EtatRepositoryAwareTrait;
use Formation\Entity\Db\Repository\InscriptionRepositoryAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;

class InscriptionSearchService extends SearchService
{
    use InscriptionRepositoryAwareTrait;
    use EtatRepositoryAwareTrait;
    use IndividuServiceAwareTrait;

    const NAME_libelle = 'libelle';
    const NAME_doctorant = 'doctorant';
    const NAME_liste = 'liste';
    const NAME_etat = 'etat';

    public function init()
    {
        $libelleFilter = $this->createLibelleFilter();
        $doctorantFilter = $this->createDoctorantFilter();
        $listeFilter = $this->createListeFilter();
        $etatFilter = $this->createEtatFilter();//->setWhereField('s.etat');
        $etatFilter->setDataProvider(fn() => array_combine(
            array_map(fn(Etat $e) => $e->getCode(), $etats = $this->etatRepository->findBy([], ['ordre' => 'asc'])),
            $etats
        ));

        $listeFilter->setData(Inscription::LISTES);

        $this->addFilters([
            $libelleFilter,
            $doctorantFilter,
            $etatFilter,
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
        $filter = new TextSearchFilter("Doctorant", self::NAME_doctorant);
        $filter->setAttributes(['title' => "Recherche sur le nom d'usage, le prénom"]);
        $filter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $individus = $this->individuService->getRepository()->findByText($filter->getValue());
            if (empty($individus)) {
                $qb->andWhere('0 = 1');
            } else {
                $individusIds = array_map(fn(array $individu) => $individu['id'], $individus);
                $qb->andWhere($qb->expr()->in('ind.id', $individusIds));
            }
        });

        return $filter;
    }

    private function createEtatFilter(): SelectSearchFilter
    {
        $filter = new SelectSearchFilter("État", self::NAME_etat);
        $filter->setWhereField('sess.etat');

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