<?php

namespace Application\Search\Financement;

trait OrigineFinancementSearchFilterAwareTrait
{
    protected ?OrigineFinancementSearchFilter $origineFinancementSearchFilter = null;

    public function getOrigineFinancementSearchFilter(): OrigineFinancementSearchFilter
    {
        if ($this->origineFinancementSearchFilter === null) {
            $this->origineFinancementSearchFilter = OrigineFinancementSearchFilter::newInstance();
        }
        return $this->origineFinancementSearchFilter;
    }

    public function setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter $origineFinancementSearchFilter): void
    {
        $this->origineFinancementSearchFilter = $origineFinancementSearchFilter;
    }
}