<?php

namespace Application\Search\These;

trait TheseTextSearchFilterAwareTrait
{
    /**
     * @var TheseTextSearchFilter
     */
    protected $theseTextSearchFilter;

    /**
     * @return TheseTextSearchFilter
     */
    public function getTheseTextSearchFilter(): TheseTextSearchFilter
    {
        if ($this->theseTextSearchFilter === null) {
            $this->theseTextSearchFilter = TheseTextSearchFilter::newInstance();
        }
        return $this->theseTextSearchFilter;
    }

    /**
     * @param TheseTextSearchFilter $theseTextSearchFilter
     */
    public function setTheseTextSearchFilter(TheseTextSearchFilter $theseTextSearchFilter)
    {
        $this->theseTextSearchFilter = $theseTextSearchFilter;
    }
}