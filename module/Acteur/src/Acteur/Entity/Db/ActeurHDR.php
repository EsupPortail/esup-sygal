<?php

namespace Acteur\Entity\Db;

use HDR\Entity\Db\HDR;

use Soutenance\Entity\Membre;

class ActeurHDR extends AbstractActeur
{
    private ?HDR $hdr = null;

    public function getHDR(): HDR
    {
        return $this->hdr;
    }

    public function setHDR(HDR $hdr): static
    {
        $this->hdr = $hdr;

        return $this;
    }

    public function getResourceId(): string
    {
        return 'ActeurHDR';
    }

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): static
    {
        $this->membre = $membre;

        return $this;
    }
}
