<?php

namespace Soutenance\Renderer;

use Application\Renderer\AbtractRendererAdapter;
use Soutenance\Entity\Membre;

class MembreSoutenanceRendererAdapter extends AbtractRendererAdapter
{
    private ?Membre $membre;

    /** @var Membre[] */
    private array $membresPouvantEtrePresidentDuJury;

    public function __construct(?Membre $membre = null)
    {
        if ($membre !== null) {
            $this->setMembre($membre);
        }
    }

    public function setMembre(Membre $membre): void
    {
        $this->membre = $membre;
    }

    public function getDenomination(): string
    {
        return $this->membre->getDenomination();
    }

    /**
     * @param Membre[] $membresPouvantEtrePresidentDuJury
     */
    public function setMembresPouvantEtrePresidentDuJury(array $membresPouvantEtrePresidentDuJury): void
    {
        $this->membresPouvantEtrePresidentDuJury = $membresPouvantEtrePresidentDuJury;
    }

    /**
     * @noinspection PhpUnused
     */
    public function getMembresPouvantEtrePresidentDuJuryAsUl(): string
    {
        return
            '<ul><li>' .
            implode(
                '</li><li>',
                array_filter(array_map(
                    fn(Membre $m) => $m->getActeur()?->__toString(),
                    $this->membresPouvantEtrePresidentDuJury
                ))
            ) .
            '</li></ul>';
    }
}