<?php

namespace Structure\Search\UniteRecherche;

trait UniteRechercheSearchFilterAwareTrait
{
    /**
     * @var UniteRechercheSearchFilter
     */
    protected $uniteRechercheSearchFilter;

    /**
     * @return UniteRechercheSearchFilter
     */
    public function getUniteRechercheSearchFilter(): UniteRechercheSearchFilter
    {
        if ($this->uniteRechercheSearchFilter === null) {
            $this->uniteRechercheSearchFilter = UniteRechercheSearchFilter::newInstance();
        }
        return $this->uniteRechercheSearchFilter;
    }

    /**
     * @param UniteRechercheSearchFilter $uniteRechercheSearchFilter
     */
    public function setUniteRechercheSearchFilter(UniteRechercheSearchFilter $uniteRechercheSearchFilter)
    {
        $this->uniteRechercheSearchFilter = $uniteRechercheSearchFilter;
    }
}