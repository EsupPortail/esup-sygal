<?php

namespace These\Renderer;

use Application\Renderer\AbtractRendererAdapter;
use Individu\Entity\Db\Individu;
use These\Entity\Db\These;

class TheseRendererAdapter extends AbtractRendererAdapter
{
    private These $these;

    public function __construct(These $these)
    {
        $this->these = $these;
    }

    public function getLibelleDiscipline(): string
    {
        return $this->these->getLibelleDiscipline();
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