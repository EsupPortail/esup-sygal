<?php

namespace These\Search\These;

trait EtatTheseSearchFilterAwareTrait
{
    /**
     * @var EtatTheseSearchFilter
     */
    protected $etatTheseSearchFilter;

    /**
     * @return EtatTheseSearchFilter
     */
    public function getEtatTheseSearchFilter(): EtatTheseSearchFilter
    {
        if ($this->etatTheseSearchFilter === null) {
            $this->etatTheseSearchFilter = EtatTheseSearchFilter::newInstance();
        }
        return $this->etatTheseSearchFilter;
    }

    /**
     * @param EtatTheseSearchFilter $etatTheseSearchFilter
     */
    public function setEtatTheseSearchFilter(EtatTheseSearchFilter $etatTheseSearchFilter)
    {
        $this->etatTheseSearchFilter = $etatTheseSearchFilter;
    }
}