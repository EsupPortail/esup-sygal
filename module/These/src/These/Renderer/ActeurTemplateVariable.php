<?php

namespace These\Renderer;

use These\Entity\Db\Acteur;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class ActeurTemplateVariable extends AbstractTemplateVariable
{
    private Acteur $acteur;

    public function setActeur(Acteur $acteur): void
    {
        $this->acteur = $acteur;
    }

    /** @noinspection PhpUnused */
    public function getDenomination(): string
    {
        return $this->acteur->getIndividu()->getNomComplet();
    }

    /** @noinspection PhpUnused */
    public function getQualite(): ?string
    {
        return $this->acteur->getQualite();
    }

    /** @noinspection PhpUnused */
    public function getEtablissementAsLibelle(): string
    {
        return ($this->acteur->getEtablissement())?$this->acteur->getEtablissement()->getStructure()->getLibelle():"<span style='background:darkred;'>Aucun établissement</span>";
    }

    /**
     * Retourne l'adresse mail de cet acteur de thèse.
     *
     * @param bool $tryMembre Faut-il en dernier ressort retourner l'email de l'eventuel Membre lié ?
     * @return string|null
     */
    public function getEmail(bool $tryMembre = false): ?string
    {
        return $this->acteur->getIndividu()->getEmailPro() ?:
            $this->acteur->getIndividu()->getEmailUtilisateur() ?:
                ($tryMembre ? $this->acteur->getMembre()?->getEmail() : null);
    }
}