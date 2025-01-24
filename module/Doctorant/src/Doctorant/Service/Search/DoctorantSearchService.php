<?php

namespace Doctorant\Service\Search;

use UnicaenSearch\Filter\SearchFilter;
use UnicaenSearch\Filter\TextSearchFilter;
use UnicaenSearch\SearchService;
use UnicaenSearch\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;

class DoctorantSearchService extends SearchService
{
    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->doctorantService->getRepository()->createQueryBuilder('d');
        $qb
            ->addSelect('i')->join('d.individu', 'i')
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->addSelect('cu')->leftJoin('i.complements', 'cu');

        return $qb;
    }

    public function init(): void
    {
        // filtre textuel
        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
        $textFilter->setAttributes(['title' => "Recherche sur le nom d'usage, le prénom, l'adresse électronique ou l'INE"]);
        $textFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $entities = $this->doctorantService->getRepository()->findByText($filter->getValue());
            if (empty($entities)) {
                $qb->andWhere('0 = 1');
            } else {
                $ids = array_map(fn(array $individu) => $individu['id'], $entities);
                $qb->andWhere($qb->expr()->in('i', $ids));
            }
        });
        $this->addFilter($textFilter);

//        $this->addSorters([
//            (new SearchSorter("Id", 'id')),
//            (new SearchSorter("Nom d'usage", 'i.nomUsuel'))->setIsDefault()
//        ]);
    }
}