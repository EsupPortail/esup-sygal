<?php

namespace Candidat\Renderer;

use Candidat\Entity\Db\Candidat;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

/**
 * Méthodes utiles seulement dans le cadre de macros de unicaen/renderer, pour ne pas polluer la classe métier
 * {@see \Candidat\Entity\Db\Candidat} avec des choses orientées "affichage".
 */
class CandidatTemplateVariable extends AbstractTemplateVariable
{
    private Candidat $candidat;

    public function setCandidat(Candidat $candidat): void
    {
        $this->candidat = $candidat;
    }

    /**
     * Pas vraiment utile de recréer la méthode __toString() ici puisqu'elle existe et a sa raison d'être
     * dans la classe d'entité, mais c'est pour l'exemple !
     *
     * @noinspection
     */
    public function __toString(): string
    {
        return (string) $this->candidat;
    }

    /**
     * @noinspection
     */
    public function getPrenom(): string
    {
        return $this->candidat->getIndividu()->getPrenom();
    }

    /**
     * @noinspection
     */
    public function getNomUsuel(): string
    {
        return $this->candidat->getIndividu()->getNomUsuel();
    }

    /**
     * @noinspection
     */
    public function getNomPatronymique(): string
    {
        return $this->candidat->getIndividu()->getNomPatronymique();
    }

    /**
     * @noinspection
     */
    public function getCivilite(): string
    {
        return $this->candidat->getIndividu()->getCivilite();
    }

    /**
     * Retourne la dénomination du candidat (civilité+nom Patronymique+prénom)
     *
     * @noinspection
     */
    public function getDenominationPatronymique(): string
    {
        return $this->candidat->getIndividu()->getNomComplet();
    }

}
