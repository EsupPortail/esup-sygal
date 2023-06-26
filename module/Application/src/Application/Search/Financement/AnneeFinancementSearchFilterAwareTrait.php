<?php

namespace Application\Search\Financement;

trait AnneeFinancementSearchFilterAwareTrait
{
    protected ?AnneeFinancementSearchFilter $anneeFinancementSearchFilter = null;

    public function getAnneeFinancementSearchFilter(): AnneeFinancementSearchFilter
    {
        if ($this->anneeFinancementSearchFilter === null) {
            $this->anneeFinancementSearchFilter = AnneeFinancementSearchFilter::newInstance();
        }
        return $this->anneeFinancementSearchFilter;
    }

    public function setAnneeFinancementSearchFilter(AnneeFinancementSearchFilter $anneeFinancementSearchFilter): void
    {
        $this->anneeFinancementSearchFilter = $anneeFinancementSearchFilter;
    }
}