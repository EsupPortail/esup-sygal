<?php

namespace These\Renderer;

use Individu\Entity\Db\Individu;
use These\Entity\Db\These;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class TheseTemplateVariable extends AbstractTemplateVariable
{
    private These $these;

    public function setThese(These $these): void
    {
        $this->these = $these;
    }

    public function getLibelleDiscipline(): string
    {
        return (string) $this->these->getDiscipline();
    }

    public function getTitre(): string
    {
        return $this->these->getTitre();
    }

    public function toStringEncadrement() : string
    {
        /** @var Individu[] $encadrement */
        $encadrement = $this->these->getEncadrements(true);
        $texte = [];
        foreach ($encadrement as $directeur) { $texte[] = $directeur->getNomComplet();}
        return implode (" et ", $texte);
    }
}