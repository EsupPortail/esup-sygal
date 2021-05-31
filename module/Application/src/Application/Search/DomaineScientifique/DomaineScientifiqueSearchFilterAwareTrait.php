<?php

namespace Application\Search\DomaineScientifique;

trait DomaineScientifiqueSearchFilterAwareTrait
{
    /**
     * @var DomaineScientifiqueSearchFilter
     */
    protected $domaineScientifiqueSearchFilter;

    /**
     * @return DomaineScientifiqueSearchFilter
     */
    public function getDomaineScientifiqueSearchFilter(): DomaineScientifiqueSearchFilter
    {
        if ($this->domaineScientifiqueSearchFilter === null) {
            $this->domaineScientifiqueSearchFilter = DomaineScientifiqueSearchFilter::newInstance();
        }
        return $this->domaineScientifiqueSearchFilter;
    }

    /**
     * @param DomaineScientifiqueSearchFilter $domaineScientifiqueSearchFilter
     */
    public function setDomaineScientifiqueSearchFilter(DomaineScientifiqueSearchFilter $domaineScientifiqueSearchFilter)
    {
        $this->domaineScientifiqueSearchFilter = $domaineScientifiqueSearchFilter;
    }
}