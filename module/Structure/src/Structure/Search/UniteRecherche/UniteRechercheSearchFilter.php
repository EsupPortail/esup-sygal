<?php

namespace Structure\Search\UniteRecherche;

use Structure\Entity\Db\UniteRecherche;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class UniteRechercheSearchFilter extends SelectSearchFilter
{
    const NAME = 'uniteRecherche';

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
        $instance = new self(
            "Unit. rech.",
            self::NAME,
            ['liveSearch' => true]
        );

        $instance->setEmptyOptionLabel("Toutes");

        return $instance;
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $alias = 'these'; // todo: rendre paramétrable

        $filterValue = $this->getValue();
        if ($filterValue === 'NULL') {
            $qb->andWhere("$alias.uniteRecherche IS NULL");
        } elseif ($filterValue) {
            $qb
                ->join("$alias.uniteRecherche", 'ur')
                ->andWhere("ur.sourceCode = :ur_sourceCode")
                ->setParameter('ur_sourceCode', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'UR éventuellement injectée est autorisée
            $ids = array_map(function(UniteRecherche $entity) { return $entity->getId(); }, $this->data);
            $qb->andWhere($qb->expr()->in("$alias.uniteRecherche", $ids));
        }
    }

    /**
     * @return SearchSorter
     *
     * todo: extraire la classe 'UniteRechercheSearchSorter'
     */
    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "Unité recherche",
            self::NAME
        );

        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'these') {
                $direction = $sorter->getDirection();
                $qb
                    ->leftJoin("$alias.uniteRecherche", 'ur_sort')
                    ->leftJoin("ur_sort.structure", 'ur_s_sort')
                    ->addOrderBy('ur_s_sort.code', $direction);
            }
        );

        return $sorter;
    }
}