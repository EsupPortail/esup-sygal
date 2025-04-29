<?php

namespace Structure\Filter;

use Laminas\Filter\AbstractFilter;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Exception\LogicException;

/**
 * Formate le nom complet d'une structure ou assimilée.
 */
class StructureFormatter extends AbstractFilter
{
    private mixed $value = null;

    private bool $avecCode = true;
    private bool $avecSigle = true;
    private bool $avecLibelle = true;
    private bool $avecFormatageLibelle = false;

    /**
     * Spécifie la structure ou assimilée dont on veut générer le libellé complet formatté.
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retourne le libellé complet formatté de la structure ou assimilée spécifié
     * via {@see setValue()}.
     */
    public function f(): string
    {
        $value = $this->value;
        $parts = [];

        // normalisation
        if ($value instanceof UniteRecherche) {
            $structure = $value->getStructure();
            $sigle = $structure->getSigle();
            $code = $structure->getCode();
            $libelle = ($this->avecFormatageLibelle) ? $this->formatLibelleUnite($structure->getLibelle(), $sigle) : $structure->getLibelle();

            if ($this->avecCode) $parts[] = $code;
            if ($this->avecLibelle) $parts[] = $libelle;
            if ($this->avecSigle && $sigle) $parts[] = sprintf(" (%s)", $sigle);
        }else if($value instanceof EcoleDoctorale){
            $structure = $value->getStructure();
            $sigle = $structure->getSigle();
            $libelle = ($this->avecFormatageLibelle) ? $this->formatLibelleUnite($structure->getLibelle(), $sigle) : $structure->getLibelle();

            if ($this->avecSigle && $sigle) $parts[] = $sigle;

            if ($this->avecLibelle) $parts[] = $libelle;
        }elseif($value instanceof Etablissement){
            $structure = $value->getStructure();
            $sigle = $structure->getSigle();
            $libelle = ($this->avecFormatageLibelle) ? $this->formatLibelleUnite($structure->getLibelle(), $sigle) : $structure->getLibelle();

            if ($this->avecSigle && $sigle) {
                if(!$this->avecLibelle || $sigle !== $libelle) $parts[] = $sigle;
            }
            if ($this->avecLibelle) $parts[] = $libelle;
        }else {
            throw new LogicException("L'objet à formatter n'est pas d'un type supporté.");
        }

        return implode(' - ', array_filter($parts));
    }

    /**
     * Génère le nom complet formatté de la structure ou assimilée spécifié en argument.
     *
     * @see f()
     */
    public function filter($value): string
    {
        return $this->setValue($value)->f();
    }

    /**
     * Active ou non l'inclusion du sigle (si il est connue).
     */
    public function avecSigle(bool $avecSigle = true): static
    {
        $this->avecSigle = $avecSigle;

        return $this;
    }

    /**
     * Active ou non l'inclusion du code.
     */
    public function avecCode(bool $avecCode = true): static
    {
        $this->avecCode = $avecCode;

        return $this;
    }

    /**
     * Active ou non l'inclusion du libellé.
     */
    public function avecLibelle(bool $avecLibelle = true): static
    {
        $this->avecLibelle = $avecLibelle;

        return $this;
    }

    /**
     * Active ou non l'inclusion du formatage du libellé.
     */
    public function avecFormatageLibelle(bool $avecFormatageLibelle = true): static
    {
        $this->avecFormatageLibelle = $avecFormatageLibelle;

        return $this;
    }

    function formatLibelleUnite(string $libelle, ?string $sigle): string
    {
        // Liste des mots courts à ne pas mettre en majuscule
        $lowerWords = [
            'de', 'des', 'du', 'la', 'le', 'les', 'et', 'à', 'au', 'aux', 'en', 'sur', 'pour', 'par', 'dans', 'avec', 'sans', 'sous', 'chez', 'vers'
        ];

        $sigle = mb_strtolower($sigle);
        // Diviser le libellé en mots
        $words = explode(' ', mb_strtolower($libelle));

        foreach ($words as $index => $word) {
            // Si c'est le premier mot, ou si le mot n'est pas dans la liste des mots courts
            // et si la première lettre du mot est présente dans le sigle,
            // ou qu'aucun sigle n'est renseigné on met en majuscule
            if ($index === 0 || !in_array($word, $lowerWords)) {
                // Vérifie que la première lettre du mot est dans le sigle
                if (($sigle && strpos($sigle, mb_substr($word, 0, 1)) !== false) || !$sigle) {
                    $words[$index] = ucfirst($word);
                } else {
                    $words[$index] = $word; // Laisser en minuscule
                }
            }
        }

        return implode(' ', $words);
    }
}