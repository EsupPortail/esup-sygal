<?php

namespace RapportActivite\Search;

use Application\Filter\AnneeUnivFormatter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class AnneeRapportActiviteSearchFilter extends SelectSearchFilter
{
    public const NAME = 'anneeRapport';

    protected string $name = self::NAME;
    protected string $label = "Année du rapport";

    /**
     * @inheritDoc
     */
    public function createValueOptionsFromData(array $data): array
    {
        $options = [];
        $options[] = $this->valueOptionEmpty();
        foreach ($data as $annee) {
            $options[] = $this->valueOptionFromString((string) $annee);
        }

        return self::formatAnneesValueOptions($options);
    }

    public function applyToQueryBuilder(QueryBuilder $qb, $alias = 'ra'): void
    {
        $qb
            ->andWhere("$alias.anneeUniv = :anneeRapportActivite")
            ->setParameter('anneeRapportActivite', $this->getValue());
    }

    /**
     * @return SearchSorter
     * @deprecated
     */
    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "Année du rapport",
            self::NAME
        );

        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'ra') {
                $direction = $sorter->getDirection();
                $qb->addOrderBy("$alias.anneeUniv", $direction);
            }
        );

        return $sorter;
    }

    /**
     * @param array $valueOptions
     * @return array
     */
    static public function formatAnneesValueOptions(array $valueOptions): array
    {
        // formattage du label, ex: "2018" devient "2018/2019"
        $f = new AnneeUnivFormatter();

        return array_map(function($value) use ($f) {
            if (! is_numeric($value['label'])) {
                return $value;
            }
            $value['label'] = $f->filter($value['label']);
            return $value;
        }, $valueOptions);
    }
}