<?php

namespace Formation\Entity\Db\Traits;

use Application\Entity\Db\Etablissement;
use Formation\Entity\Db\Interfaces\HasSiteInterface;

trait HasSiteTrait {

    /** @var Etablissement|null */
    private $site;

    /**
     * @return Etablissement|null
     */
    public function getSite(): ?Etablissement
    {
        return $this->site;
    }

    /**
     * @param Etablissement|null $site
     * @return HasSiteInterface
     */
    public function setSite(?Etablissement $site): HasSiteInterface
    {
        $this->site = $site;
        return $this;
    }
}