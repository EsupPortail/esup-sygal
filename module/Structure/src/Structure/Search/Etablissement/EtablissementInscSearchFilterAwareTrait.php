<?php

namespace Structure\Search\Etablissement;

trait EtablissementInscSearchFilterAwareTrait
{
    protected ?EtablissementSearchFilter $etablissementInscSearchFilter = null;

    public function getEtablissementInscSearchFilter(): EtablissementSearchFilter
    {
        if ($this->etablissementInscSearchFilter === null) {
            $this->etablissementInscSearchFilter = new EtablissementSearchFilter();
        }
        return $this->etablissementInscSearchFilter;
    }

    public function setEtablissementInscSearchFilter(EtablissementSearchFilter $etablissementInscSearchFilter): void
    {
        $this->etablissementInscSearchFilter = $etablissementInscSearchFilter;
    }
}