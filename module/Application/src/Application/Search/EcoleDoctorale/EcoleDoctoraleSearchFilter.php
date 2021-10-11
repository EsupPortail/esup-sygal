<?php

namespace Application\Search\EcoleDoctorale;

use Application\Entity\Db\EcoleDoctorale;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EcoleDoctoraleSearchFilter extends SelectSearchFilter
{
    const NAME = 'ecoleDoctorale';

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
            "École doct.",
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
            $qb->andWhere("$alias.ecoleDoctorale IS NULL");
        } elseif ($filterValue) {
            $qb
                ->join("$alias.ecoleDoctorale", 'ed')
                ->andWhere("ed.sourceCode = :ed_sourceCode")
                ->setParameter('ed_sourceCode', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'ED éventuellement injectée est autorisée
            $ids = array_map(function(EcoleDoctorale $entity) { return $entity->getId(); }, $this->data);
            $qb->andWhere($qb->expr()->in("$alias.ecoleDoctorale", $ids));
        }
    }

    /**
     * @return SearchSorter
     *
     * todo: extraire la classe 'EcoleDoctoraleSearchSorter'
     */
    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "Ecole doctorale",
            self::NAME
        );

        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'these') {
                $direction = $sorter->getDirection();
                $qb
                    ->leftJoin("$alias.ecoleDoctorale", 'ed_sort')
                    ->leftJoin("ed_sort.structure", 'ed_s_sort')
                    ->addOrderBy('ed_s_sort.code', $direction);
            }
        );

        return $sorter;
    }
}