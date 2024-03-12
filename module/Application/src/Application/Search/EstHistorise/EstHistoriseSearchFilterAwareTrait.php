<?php

namespace Application\Search\EstHistorise;

trait EstHistoriseSearchFilterAwareTrait
{
    protected ?EstHistoriseSearchFilter $estHistoriseSearchFilter = null;

    public function getEstHistoriseSearchFilter(): EstHistoriseSearchFilter
    {
        if ($this->estHistoriseSearchFilter === null) {
            $this->estHistoriseSearchFilter = EstHistoriseSearchFilter::newInstance();
        }
        return $this->estHistoriseSearchFilter;
    }

    public function setEstHistoriseSearchFilter(EstHistoriseSearchFilter $domaineScientifiqueSearchFilter): void
    {
        $this->estHistoriseSearchFilter = $domaineScientifiqueSearchFilter;
    }
}