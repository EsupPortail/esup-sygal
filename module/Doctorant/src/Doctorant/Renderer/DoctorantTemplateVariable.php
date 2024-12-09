<?php

namespace Doctorant\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Doctorant\Entity\Db\Doctorant;
use Individu\Renderer\IndividuTemplateVariable;

/**
 * Méthodes utiles seulement dans le cadre de macros de unicaen/renderer, pour ne pas polluer la classe métier
 * {@see \Doctorant\Entity\Db\Doctorant} avec des choses orientées "affichage".
 *
 * @property Doctorant $target
 */
class DoctorantTemplateVariable extends AbstractTemplateVariable
{
    public function setDoctorant(Doctorant $doctorant): void
    {
        $this->setTarget($doctorant);
    }

    /**
     * Pas vraiment utile de recréer la méthode __toString() ici puisqu'elle existe et a sa raison d'être
     * dans la classe d'entité, mais c'est pour l'exemple !
     *
     * @noinspection
     */
    public function __toString(): string
    {
        return (string) $this->target;
    }

    /**
     * @noinspection
     */
    public function getPrenom(): string
    {
        return $this->target->getIndividu()->getPrenom();
    }

    /**
     * @noinspection
     */
    public function getNomUsuel(): string
    {
        return $this->target->getIndividu()->getNomUsuel();
    }

    /**
     * @noinspection
     */
    public function getNomPatronymique(): string
    {
        return $this->target->getIndividu()->getNomPatronymique();
    }

    /**
     * @noinspection
     */
    public function getCivilite(): string
    {
        return $this->target->getIndividu()->getCivilite();
    }

    /**
     * Retourne la dénomination du doctorant (civilité+nom Patronymique+prénom)
     *
     * @noinspection
     */
    public function getDenominationPatronymique(): string
    {
        return $this->target->getIndividu()->getNomCompletFormatter()->avecNomPatronymique()->f();
    }

    public function getIndividu(): IndividuTemplateVariable
    {
        /** @var IndividuTemplateVariable $templateVariable */
        $templateVariable = $this->pluginManager->get(IndividuTemplateVariable::class);
        $templateVariable->setIndividu($this->target->getIndividu());

        return $templateVariable;
    }
}
