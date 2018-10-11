<?php

namespace Application\Filter;

trait EtablissementPrefixFilterAwareTrait
{
    /**
     * @var EtablissementPrefixFilter
     */
    protected $etablissementPrefixFilter;

    /**
     * @return EtablissementPrefixFilter
     */
    public function getEtablissementPrefixFilter()
    {
        if (null === $this->etablissementPrefixFilter) {
            $this->etablissementPrefixFilter = new EtablissementPrefixFilter();
        }

        return $this->etablissementPrefixFilter;
    }
}