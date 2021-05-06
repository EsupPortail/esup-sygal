<?php

namespace Application\Service\Utilisateur;

use Application\Search\Filter\CheckboxSearchFilter;
use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class UtilisateurSearchService extends SearchService
{
    use UtilisateurServiceAwareTrait;

    /**
     * @inheritDoc
     */
    public function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->utilisateurService->getRepository()->createQueryBuilder('u');
        $qb
            ->addSelect('i')
            ->leftJoin('u.individu', 'i');

        return $qb;
    }

    public function init()
    {
        // filtre textuel
        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
        $textFilter->setAttributes(['title' => "Recherche sur le Display name, Username, Email ou Individu"]);
        $textFilter->setApplyToQueryBuilderCallable(function(SearchFilter $filter, QueryBuilder $qb) {
            $qb
                ->andWhere($qb->expr()->orX(
                    'UPPER(u.username)    LIKE UPPER(:text)',
                    'UPPER(u.displayName) LIKE UPPER(:text)',
                    'UPPER(u.email)       LIKE UPPER(:text)'
                ))
                ->setParameter('text', '%' . $filter->getValue() . '%');
        });
        $this->addFilter($textFilter);

        // filtre "est lié à un individu"
        $individuFilter = new CheckboxSearchFilter("Lié à un<br>individu", 'individu');
        $individuFilter->setApplyToQueryBuilderCallable(function(SearchFilter $filter, QueryBuilder $qb) {
            $qb->andWhere('i IS NOT NULL');
        });
        $this->addFilter($individuFilter);

        // filtre "est lié à un individu supprimé"
        $individuSupprFilter = new CheckboxSearchFilter("Lié à un<br>individu supprimé", 'individu_suppr');
        $individuSupprFilter->setApplyToQueryBuilderCallable(function(SearchFilter $filter, QueryBuilder $qb) {
            $qb->andWhere('pasHistorise(i) = 0');
        });
        $this->addFilter($individuSupprFilter);

        $this->addSorters([
            (new SearchSorter("Display name", 'displayName'))->setIsDefault()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function fetchValueOptionsForSelectFilter(SelectSearchFilter $filter): array
    {
        // aucun filtre à peupler
        return [];
    }
}