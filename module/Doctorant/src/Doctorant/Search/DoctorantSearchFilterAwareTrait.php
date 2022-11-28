<?php

namespace Doctorant\Search;

trait DoctorantSearchFilterAwareTrait
{
    /**
     * @var DoctorantSearchFilter
     */
    protected DoctorantSearchFilter $doctorantSearchFilter;

    /**
     * @return DoctorantSearchFilter
     */
    public function getDoctorantSearchFilter(): DoctorantSearchFilter
    {
        if ($this->doctorantSearchFilter === null) {
            $this->doctorantSearchFilter = DoctorantSearchFilter::newInstance();
        }
        return $this->doctorantSearchFilter;
    }

    /**
     * @param DoctorantSearchFilter $doctorantSearchFilter
     */
    public function setDoctorantSearchFilter(DoctorantSearchFilter $doctorantSearchFilter)
    {
        $this->doctorantSearchFilter = $doctorantSearchFilter;
    }
}