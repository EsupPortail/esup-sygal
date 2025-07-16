<?php

namespace These\Renderer;

use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Doctorant\Renderer\DoctorantTemplateVariable;
use Individu\Entity\Db\Individu;
use Structure\Renderer\StructureTemplateVariable;
use These\Entity\Db\These;

/**
 * @property \These\Entity\Db\These $target
 */
class TheseTemplateVariable extends AbstractTemplateVariable
{
    public function setThese(These $these): void
    {
        $this->setTarget($these);
    }

    public function getDoctorant(): DoctorantTemplateVariable
    {
        /** @var DoctorantTemplateVariable $templateVariable */
        $templateVariable = $this->pluginManager->get(DoctorantTemplateVariable::class);
        $templateVariable->setDoctorant($this->target->getDoctorant());

        return $templateVariable;
    }
//    /**
//     * Autre possibilité : Retourner le Doctorant, ce qui rend accessibles sans contrainte les données Doctorant !
//     * À faire en conscience !
//     */
//    public function getDoctorant(): Doctorant
//    {
//        return $this->target->getDoctorant();
//    }

    public function getEtablissement(): StructureTemplateVariable
    {
        /** @var StructureTemplateVariable $templateVariable */
        $templateVariable = $this->pluginManager->get(StructureTemplateVariable::class);
        $templateVariable->setStructureConcrete($this->target->getEtablissement());

        return $templateVariable;
    }

    public function getEcoleDoctorale(): StructureTemplateVariable
    {
        /** @var StructureTemplateVariable $templateVariable */
        $templateVariable = $this->pluginManager->get(StructureTemplateVariable::class);
        $templateVariable->setStructureConcrete($this->target->getEcoleDoctorale());

        return $templateVariable;
    }

    public function getLibelleDiscipline(): string
    {
        return (string) $this->target->getDiscipline();
    }

    public function getTitre(): string
    {
        return $this->target->getTitre();
    }

    public function toStringEncadrement() : string
    {
        /** @var Individu[] $encadrement */
        $encadrement = $this->target->getEncadrements(true);
        $texte = [];
        foreach ($encadrement as $directeur) { $texte[] = $directeur->getNomComplet();}
        return implode (" et ", $texte);
    }
}