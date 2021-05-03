<?php

namespace Application\Service\UniteRecherche;

use Application\Entity\Db\UniteRecherche;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class UniteRechercheSearchFilter extends SelectSearchFilter
{
    const NAME = 'unite_recherche';

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
            "Unité de recherche",
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
        $options[] = $this->valueOptionEmpty("Toutes");
        foreach ($data as $ur) {
            $options[] = $this->valueOptionEntity($ur, function(UniteRecherche $ur) {
                return $ur->getStructure()->getCode() . ' - ' . $ur->getLibelle();
            });
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
            $qb->andWhere("$alias.uniteRecherche IS NULL");
        } elseif ($filterValue) {
            $qb->andWhere("$alias.uniteRecherche = :ur")->setParameter('ur', $filterValue);
        }

        if ($this->data !== null) {
            // garantit que l'UR éventuellement injectée est autorisée
            $ids = array_map(function(UniteRecherche $value) { return $value->getId(); }, $this->data);
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

        $sorter->setApplyToQueryBuilderCallable(
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