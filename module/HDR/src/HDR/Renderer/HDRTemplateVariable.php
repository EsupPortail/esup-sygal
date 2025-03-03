<?php

namespace HDR\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;

class HDRTemplateVariable extends AbstractTemplateVariable
{
    private HDR $hdr;

    public function setHDR(HDR $hdr): void
    {
        $this->hdr = $hdr;
    }

    public function getLibelleDiscipline(): ?string
    {
        return $this->hdr->getDiscipline()?->getLibelle();
    }

    public function getLibelleVersionDiplome(): ?string
    {
        return $this->hdr->getVersionDiplome()?->getLibelleLong();
    }

    public function toStringEncadrement() : string
    {
        /** @var Individu[] $encadrement */
        $encadrement = $this->hdr->getEncadrements(true);
        $texte = [];
        foreach ($encadrement as $directeur) { $texte[] = $directeur->getNomComplet();}
        return implode (" et ", $texte);
    }
}