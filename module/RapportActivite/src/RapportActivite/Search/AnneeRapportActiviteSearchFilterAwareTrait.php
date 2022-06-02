<?php

namespace RapportActivite\Search;

use RapportActivite\Search\AnneeRapportActiviteSearchFilter;

trait AnneeRapportActiviteSearchFilterAwareTrait
{
    /**
     * @var AnneeRapportActiviteSearchFilter
     */
    protected $anneeRapportActiviteSearchFilter;

    /**
     * @return AnneeRapportActiviteSearchFilter
     */
    public function getAnneeRapportActiviteSearchFilter(): AnneeRapportActiviteSearchFilter
    {
        if ($this->anneeRapportActiviteSearchFilter === null) {
            $this->anneeRapportActiviteSearchFilter = AnneeRapportActiviteSearchFilter::newInstance();
        }
        return $this->anneeRapportActiviteSearchFilter;
    }

    /**
     * @param \RapportActivite\Search\AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter
     */
    public function setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter)
    {
        $this->anneeRapportActiviteSearchFilter = $anneeRapportActiviteSearchFilter;
    }
}