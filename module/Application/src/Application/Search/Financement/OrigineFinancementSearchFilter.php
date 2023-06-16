<?php

namespace Application\Search\Financement;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;

class OrigineFinancementSearchFilter extends SelectSearchFilter
{
    const NAME = 'financement';

    private ?AnneeFinancementSearchFilter $anneeFinancementSearchFilter = null;

    /**
     * Spécifie le filtre "année de financement" dont on doit utiliser la valeur.
     */
    public function setAnneeFinancementSearchFilter(?AnneeFinancementSearchFilter $anneeFinancementSearchFilter): self
    {
        $this->anneeFinancementSearchFilter = $anneeFinancementSearchFilter;
        return $this;
    }

    static public function newInstance(): self
    {
        return new self(
            "Orig. financ.",
            self::NAME,
            ['liveSearch' => true]
        );
    }

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $alias = 'these'; // todo: rendre paramétrable

        $filterValue = $this->getValue();

        $qb
            ->leftJoin("$alias.financements", 'fin')
            ->leftJoin('fin.origineFinancement', 'orig')
            ->andWhere("fin.histoDestruction is null")
        ;
        if ($filterValue === 'NULL') {
            $qb
                ->andWhere('orig.id IS NULL');
        } else {
            $qb
                ->andWhere('orig.code = :origine')
                ->setParameter('origine', $filterValue);
        }

        //
        // Prise en compte du filtre "annee de financement" éventuel.
        //
        if ($this->anneeFinancementSearchFilter !== null) {
            if ($this->anneeFinancementSearchFilter->canApplyToQueryBuilder()) {
                $qb
                    ->andWhere('fin.annee = :anneeFinanc')
                    ->setParameter('anneeFinanc', $this->anneeFinancementSearchFilter->getValue());
                // le filtre devient redondant
                $this->anneeFinancementSearchFilter->setValue(null);
            }
        }
    }
}