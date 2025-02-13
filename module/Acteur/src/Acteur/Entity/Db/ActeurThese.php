<?php

namespace Acteur\Entity\Db;

use Soutenance\Entity\Membre;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;

class ActeurThese extends AbstractActeur
{
    protected ?string $libelleQualite = null;
    protected bool $principal = false;

    private ?These $these = null;
    private ?Etablissement $etablissementForce = null;

    public function getThese(): ?These
    {
        return $this->these;
    }

    public function setThese(These $these): static
    {
        $this->these = $these;

        return $this;
    }

    public function setEtablissementForce(?Etablissement $etablissementForce): self
    {
        $this->etablissementForce = $etablissementForce;
        return $this;
    }

    public function getEtablissementForce(): ?Etablissement
    {
        return $this->etablissementForce;
    }

    public function getResourceId(): string
    {
        return 'ActeurThese';
    }

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(\Soutenance\Entity\Membre $membre): static
    {
        $this->membre = $membre;

        return $this;
    }
}
