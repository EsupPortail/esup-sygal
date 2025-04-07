<?php

namespace These\Search\These;

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
            $this->theseTextSearchFilter = new TheseTextSearchFilter();
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