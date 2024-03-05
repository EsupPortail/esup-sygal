<?php

namespace Admission\Search\Admission;

trait EtatAdmissionSearchFilterAwareTrait
{
    /**
     * @var EtatAdmissionSearchFilter
     */
    protected $etatAdmissionSearchFilter;

    /**
     * @return EtatAdmissionSearchFilter
     */
    public function getEtatAdmissionSearchFilter(): EtatAdmissionSearchFilter
    {
        if ($this->etatAdmissionSearchFilter === null) {
            $this->etatAdmissionSearchFilter = EtatAdmissionSearchFilter::newInstance();
        }
        return $this->etatAdmissionSearchFilter;
    }

    /**
     * @param EtatAdmissionSearchFilter $etatAdmissionSearchFilter
     */
    public function setEtatAdmissionSearchFilter(EtatAdmissionSearchFilter $etatAdmissionSearchFilter)
    {
        $this->etatAdmissionSearchFilter = $etatAdmissionSearchFilter;
    }
}