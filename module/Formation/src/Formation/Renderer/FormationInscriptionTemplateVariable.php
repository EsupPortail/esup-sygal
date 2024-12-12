<?php

namespace Formation\Renderer;

use Formation\Entity\Db\Inscription;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class FormationInscriptionTemplateVariable extends AbstractTemplateVariable
{
    private Inscription $inscription;

    public function setInscription(Inscription $inscription): void
    {
        $this->inscription = $inscription;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDureeSuivie() : string
    {
        $duree = $this->inscription->computeDureePresence();
        return "".$duree;
    }
}