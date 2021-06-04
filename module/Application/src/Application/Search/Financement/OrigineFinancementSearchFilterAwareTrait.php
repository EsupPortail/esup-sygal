<?php

namespace Application\Search\Financement;

trait OrigineFinancementSearchFilterAwareTrait
{
    /**
     * @var OrigineFinancementSearchFilter
     */
    protected $origineFinancementSearchFilter;

    /**
     * @return OrigineFinancementSearchFilter
     */
    public function getOrigineFinancementSearchFilter(): OrigineFinancementSearchFilter
    {
        if ($this->origineFinancementSearchFilter === null) {
            $this->origineFinancementSearchFilter = OrigineFinancementSearchFilter::newInstance();
        }
        return $this->origineFinancementSearchFilter;
    }

    /**
     * @param OrigineFinancementSearchFilter $origineFinancementSearchFilter
     */
    public function setOrigineFinancementSearchFilter(OrigineFinancementSearchFilter $origineFinancementSearchFilter)
    {
        $this->origineFinancementSearchFilter = $origineFinancementSearchFilter;
    }
}