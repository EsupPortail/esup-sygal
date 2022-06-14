<?php

namespace Structure\Search\Etablissement;

use Structure\Entity\Db\Etablissement;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EtablissementSearchFilter extends SelectSearchFilter
{
    const NAME = 'etablissement';

    /**
     * @inheritDoc
     */
    protected function __construct(string $label, string $name, array $attributes = [], $defaultValue = null)
    {
        parent::__construct($label, $name, $attributes, $defaultValue);
    }

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        return new self(
            "Étab. d'inscr.",
            self::NAME
        );
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $alias = 'these'; // todo: rendre paramétrable

        $qb
            ->join("$alias.etablissement", 'etab')
            ->andWhere("etab.sourceCode = :etab_sourceCode")
            ->setParameter('etab_sourceCode', $this->getValue());

        if ($this->data !== null) {
            // garantit que l'étab éventuellement injecté est autorisé
            $ids = array_map(function(Etablissement $entity) { return $entity->getId(); }, $this->data);
            $qb->andWhere($qb->expr()->in('etab', $ids));
        }
    }

    /**
     * @return SearchSorter
     */
    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "Établissement<br>d'inscr.",
            self::NAME
        );

        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'these') {
                $direction = $sorter->getDirection();
                $qb
                    ->join("$alias.etablissement", 'e_sort')
                    ->join('e_sort.structure', 's_sort')
                    ->addOrderBy('s_sort.code', $direction);
            }
        );

        return $sorter;
    }
}