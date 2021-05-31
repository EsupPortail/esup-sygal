<?php

namespace Application\Search\Etablissement;

use Application\Search\Etablissement\EtablissementSearchFilter;

trait EtablissementInscSearchFilterAwareTrait
{
    /**
     * @var EtablissementSearchFilter
     */
    protected $etablissementInscSearchFilter;

    /**
     * @return EtablissementSearchFilter
     */
    public function getEtablissementInscSearchFilter(): EtablissementSearchFilter
    {
        if ($this->etablissementInscSearchFilter === null) {
            $this->etablissementInscSearchFilter = EtablissementSearchFilter::newInstance();
        }
        return $this->etablissementInscSearchFilter;
    }

    /**
     * @param EtablissementSearchFilter $etablissementInscSearchFilter
     */
    public function setEtablissementInscSearchFilter(EtablissementSearchFilter $etablissementInscSearchFilter)
    {
        $this->etablissementInscSearchFilter = $etablissementInscSearchFilter;
    }
}