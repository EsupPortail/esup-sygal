<?php

namespace Acteur\Renderer;

use Acteur\Entity\Db\AbstractActeur;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class ActeurTemplateVariable extends AbstractTemplateVariable
{
    private AbstractActeur $acteur;

    public function setActeur(AbstractActeur $acteur): void
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