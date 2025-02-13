<?php

namespace HDR\Search\HDR;

trait EtatHDRSearchFilterAwareTrait
{
    /**
     * @var EtatHDRSearchFilter
     */
    protected $etatHDRSearchFilter;

    /**
     * @return EtatHDRSearchFilter
     */
    public function getEtatHDRSearchFilter(): EtatHDRSearchFilter
    {
        if ($this->etatHDRSearchFilter === null) {
            $this->etatHDRSearchFilter = EtatHDRSearchFilter::newInstance();
        }
        return $this->etatHDRSearchFilter;
    }

    /**
     * @param EtatHDRSearchFilter $etatHDRSearchFilter
     */
    public function setEtatHDRSearchFilter(EtatHDRSearchFilter $etatHDRSearchFilter)
    {
        $this->etatHDRSearchFilter = $etatHDRSearchFilter;
    }
}