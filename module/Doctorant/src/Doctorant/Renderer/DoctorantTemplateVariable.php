<?php

namespace Doctorant\Renderer;

use Doctorant\Entity\Db\Doctorant;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

/**
 * Méthodes utiles seulement dans le cadre de macros de unicaen/renderer, pour ne pas polluer la classe métier
 * {@see \Doctorant\Entity\Db\Doctorant} avec des choses orientées "affichage".
 */
class DoctorantTemplateVariable extends AbstractTemplateVariable
{
    private Doctorant $doctorant;

    public function setDoctorant(Doctorant $doctorant): void
    {
        $this->doctorant = $doctorant;
    }

    /**
     * Pas vraiment utile de recréer la méthode __toString() ici puisqu'elle existe et a sa raison d'être
     * dans la classe d'entité, mais c'est pour l'exemple !
     *
     * @noinspection
     */
    public function __toString(): string
    {
        return (string) $this->doctorant;
    }

    /**
     * @noinspection
     */
    public function getPrenom(): string
    {
        return $this->doctorant->getIndividu()->getPrenom();
    }

    /**
     * @noinspection
     */
    public function getNomUsuel(): string
    {
        return $this->doctorant->getIndividu()->getNomUsuel();
    }

    /**
     * @noinspection
     */
    public function getNomPatronymique(): string
    {
        return $this->doctorant->getIndividu()->getNomPatronymique();
    }

    /**
     * @noinspection
     */
    public function getCivilite(): string
    {
        return $this->doctorant->getIndividu()->getCivilite();
    }

    /**
     * Retourne la dénomination du doctorant (civilité+nom Patronymique+prénom)
     *
     * @noinspection
     */
    public function getDenominationPatronymique(): string
    {
        return $this->doctorant->getIndividu()->getNomComplet();
    }

}
