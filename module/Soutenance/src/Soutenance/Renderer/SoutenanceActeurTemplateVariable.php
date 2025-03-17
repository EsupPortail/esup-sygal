<?php

namespace Soutenance\Renderer;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use InvalidArgumentException;
use Soutenance\Entity\Membre;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;
use Webmozart\Assert\Assert;

class SoutenanceActeurTemplateVariable extends AbstractTemplateVariable
{
    /** @var ActeurThese[]|ActeurHDR[] */
    private array $acteursPouvantEtrePresidentDuJury;

    /**
     * @param Membre[] $acteursPouvantEtrePresidentDuJury
     */
    public function setActeursPouvantEtrePresidentDuJury(array $acteursPouvantEtrePresidentDuJury): void
    {
        if (count($acteursPouvantEtrePresidentDuJury) === 0) {
            throw new InvalidArgumentException("La liste des acteurs pouvant être président du jury ne peut être vide");
        }

        array_map(function ($acteur) {
            Assert::true(
                $acteur instanceof ActeurThese || $acteur instanceof ActeurHDR,
                'Tous les acteurs doivent être des instances de ActeurThese ou ActeurHDR'
            );
        }, $acteursPouvantEtrePresidentDuJury);

        $this->acteursPouvantEtrePresidentDuJury = $acteursPouvantEtrePresidentDuJury;
    }

    /**
     * @noinspection PhpUnused
     */
    public function getActeursPouvantEtrePresidentDuJuryAsUl(): string
    {
        return
            '<ul><li>' .
            implode(
                '</li><li>',
                array_filter(array_map(
                    fn(ActeurThese|ActeurHDR $a) => $a->getMembre()->getNom() ? (mb_strtoupper($a->getMembre()->getNom()) . ' ' . $a->getMembre()->getPrenom()) : null,
                    $this->acteursPouvantEtrePresidentDuJury
                ))
            ) .
            '</li></ul>';
    }
}