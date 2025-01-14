<?php

namespace Formation\Renderer;

use Formation\Entity\Db\Formation;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class FormationTemplateVariable extends AbstractTemplateVariable
{
    private Formation $formation;

    public function setFormation(Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function getLibelle(): string
    {
        return $this->formation->getLibelle();
    }

    /** @noinspection  PhpUnused */
    public function toStringResponsable() : string
    {
        if ($this->formation->getResponsable() === null) return "Aucun·e responsable nommé·e pour cette formation";
        return $this->formation->getResponsable()->getNomComplet();
    }
}