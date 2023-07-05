<?php

namespace Doctorant\Service\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\TextSearchFilter;
use Application\Search\SearchService;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;

class DoctorantSearchService extends SearchService
{
    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;

    public function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->doctorantService->getRepository()->createQueryBuilder('d');
        $qb
            ->addSelect('i')
            ->join('d.individu', 'i')
            ->addSelect('u')
            ->leftJoin('i.utilisateurs', 'u')
            ->addSelect('cu')
            ->leftJoin('i.complements', 'cu');

        return $qb;
    }

    public function init()
    {
        // filtre textuel
        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
        $textFilter->setAttributes(['title' => "Recherche sur le nom d'usage, le prénom ou l'adresse électronique"]);
        $textFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $individus = $this->individuService->getRepository()->findByText($filter->getValue());
            if (empty($individus)) {
                $qb->andWhere('0 = 1');
            } else {
                $individusIds = array_map(fn(array $individu) => $individu['id'], $individus);
                $qb->andWhere($qb->expr()->in('i', $individusIds));
            }
        });
        $this->addFilter($textFilter);

        $this->addSorters([
            (new SearchSorter("Id", 'id')),
            (new SearchSorter("Nom d'usage", 'i.nomUsuel'))->setIsDefault()
        ]);
    }
}