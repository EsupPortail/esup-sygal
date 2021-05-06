<?php

namespace Application\Service\EcoleDoctorale;

use Application\Entity\Db\EcoleDoctorale;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EcoleDoctoraleSearchFilter extends SelectSearchFilter
{
    const NAME = 'ecole_doctorale';

    /**
     * @inheritDoc
     */
    protected function __construct(string $label, string $name, array $options, array $attributes = [], $defaultValue = null)
    {
        parent::__construct($label, $name, $options, $attributes, $defaultValue);
    }

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        $instance = new self(
            "École doctorale",
            self::NAME,
            [],
            ['liveSearch' => true]
        );

        $instance->setEmptyOptionLabel("Toutes");

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function createValueOptionsFromData(array $data): array
    {
        $options = [];
        if ($this->allowsEmptyOption()) {
            $options[] = $this->valueOptionEmpty($this->getEmptyOptionLabel());
        }
        foreach ($data as $ed) {
            $options[] = $this->valueOptionEntity($ed);
        }

        return $options;
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
            $qb->andWhere("$alias.ecoleDoctorale = :ed")->setParameter('ed', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'ED éventuellement injectée est autorisée
            $ids = array_map(function(EcoleDoctorale $ed) { return $ed->getId(); }, $this->data);
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

        $sorter->setApplyToQueryBuilderCallable(
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