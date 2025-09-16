<?php

namespace Application\Service\Utilisateur;

use UnicaenSearch\Filter\CheckboxSearchFilter;
use UnicaenSearch\Filter\SearchFilter;
use UnicaenSearch\Filter\TextSearchFilter;
use UnicaenSearch\SearchService;
use UnicaenSearch\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class UtilisateurSearchService extends SearchService
{
    use UtilisateurServiceAwareTrait;

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->utilisateurService->getRepository()->createQueryBuilder('u');
        $qb
            ->addSelect('i')
            ->leftJoin('u.individu', 'i');

        return $qb;
    }

    public function init(): void
    {
        // filtre textuel
        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
        $textFilter->setAttributes(['title' => "Recherche sur l'identité, l'identifiant de connexion, l'adresse électronique, ou l'utilisateur lié"]);
        $textFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $utilisateurs = $this->utilisateurService->getRepository()->findByText($filter->getValue(), limit: 0);
            if (count($utilisateurs) > 0) {
                $utilisateursIds = array_map(fn(array $utilisateur) => $utilisateur['id'], $utilisateurs);
                $qb->andWhere($qb->expr()->in('u.id', $utilisateursIds));
            }
        });
        $this->addFilter($textFilter);

//        // filtre textuel
//        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
//        $textFilter->setAttributes(['title' => "Recherche sur le Display name, Username, Email ou Individu"]);
//        $textFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
//            $qb
//                ->andWhere($qb->expr()->orX(
//                    'UPPER(u.username)    LIKE UPPER(:text)',
//                    'UPPER(u.displayName) LIKE UPPER(:text)',
//                    'UPPER(u.email)       LIKE UPPER(:text)'
//                ))
//                ->setParameter('text', '%' . $filter->getValue() . '%');
//        });
//        $this->addFilter($textFilter);

        // filtre "est lié à un individu"
        $individuFilter = new CheckboxSearchFilter("Lié à un<br>individu", 'individu');
        $individuFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            if ($filter->getValue() === true) {
                $qb->andWhere('i IS NOT NULL');
            }
        });
        $this->addFilter($individuFilter);

        // filtre "est lié à un individu supprimé"
        $individuSupprFilter = new CheckboxSearchFilter("Lié à un<br>individu supprimé", 'individu_suppr');
        $individuSupprFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            if ($filter->getValue() === true) {
                $qb->andWhere('i.histoDestruction is not null');
            }
        });
        $this->addFilter($individuSupprFilter);

        $this->addSorters([
            (new SearchSorter("Display name", 'displayName'))->setIsDefault()
        ]);
    }
}