<?php

namespace Doctorant\Service\Search;

use UnicaenSearch\Filter\SearchFilter;
use UnicaenSearch\Filter\TextSearchFilter;
use UnicaenSearch\SearchService;
use UnicaenSearch\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Structure\Search\Etablissement\EtablissementInscSearchFilterAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class DoctorantSearchService extends SearchService
{
    use IndividuServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use EtablissementInscSearchFilterAwareTrait;

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->doctorantService->getRepository()->createQueryBuilder('d');
        $qb
            ->addSelect('etab')->join('d.etablissement', 'etab')
            ->addSelect('i')->join('d.individu', 'i')
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->addSelect('cu')->leftJoin('i.complements', 'cu')
            ->leftJoin('d.theses', 'these')->andWhereNotHistorise('these')
            ->leftJoin('these.acteurs', 'act')->andWhereNotHistorise('act')
            ->leftJoin('act.role', 'actr')->andWhereNotHistorise('actr')
            ->andWhereNotHistorise('d');

        return $qb;
    }

    public function init(): void
    {
        // filtre établissement
        $etablissementInscrFilter = $this->getEtablissementInscSearchFilter()
            ->setDataProvider(function() {
                return $this->fetchEtablissements();
            })
            ->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb, string $alias = 'd') {
                $qb
                    ->andWhere($qb->expr()->orX('etab.sourceCode = :sourceCodeEtab'))
                    ->setParameter('sourceCodeEtab', $filter->getValue());
            });
        $this->addFilter($etablissementInscrFilter);

        // filtre textuel
        $textFilter = new TextSearchFilter("Recherche textuelle", 'text');
        $textFilter->setAttributes(['title' => "Recherche sur le nom d'usage, le prénom, l'adresse électronique ou l'INE"]);
        $textFilter->setQueryBuilderApplier(function(SearchFilter $filter, QueryBuilder $qb) {
            $entities = $this->doctorantService->getRepository()->findByText($filter->getValue());
            if (empty($entities)) {
                $qb->andWhere('0 = 1');
            } else {
                $ids = array_map(fn(array $individu) => $individu['id'], $entities);
                $qb->andWhere($qb->expr()->in('d', $ids));
            }
        });
        $this->addFilter($textFilter);

        $this->addSorters([
            (new SearchSorter("Id", 'id')),
            (new SearchSorter("Nom d'usage", 'nomUsuel'))->setOrderByField('i.nomUsuel')->setIsDefault()
        ]);
    }

    private function fetchEtablissements(): array
    {
        return $this->etablissementService->getRepository()->findAllEtablissementsInscriptions(true);
    }
}