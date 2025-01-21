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

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getPositionListeComplementaire() : int
    {
        if ($this->inscription->getListe() !== Inscription::LISTE_COMPLEMENTAIRE OR $this->inscription->estHistorise()) return -1;

        $liste = $this->inscription->getSession()->getListeComplementaire();
        usort($liste, function(Inscription $a, Inscription $b) { return $a->getHistoCreation() > $b->getHistoCreation(); });

        for ($i = 0 ; $i < count($liste) ; $i++) {
            if ($liste[$i] === $this) return ($i+1);
        }
        return -1;
    }
}