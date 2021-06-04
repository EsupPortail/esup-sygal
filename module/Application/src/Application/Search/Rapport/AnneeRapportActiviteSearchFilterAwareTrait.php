<?php

namespace Application\Search\Rapport;

use Application\Search\Rapport\AnneeRapportActiviteSearchFilter;

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
     * @param AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter
     */
    public function setAnneeRapportActiviteSearchFilter(AnneeRapportActiviteSearchFilter $anneeRapportActiviteSearchFilter)
    {
        $this->anneeRapportActiviteSearchFilter = $anneeRapportActiviteSearchFilter;
    }
}