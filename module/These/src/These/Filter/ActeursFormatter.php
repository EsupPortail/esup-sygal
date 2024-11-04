<?php

namespace These\Filter;

use Laminas\Filter\AbstractFilter;
use Laminas\View\Helper\HtmlList;
use LogicException;
use These\Entity\Db\Acteur;

/**
 * @var bool $asUl                      returned data type as unordered list (html)
 * @var bool $asSeparated               returned data type as separated value
 * @var bool $asArray                   returned data type as array
 * @var string $separator               separator used in the separated value format
 */
class ActeursFormatter extends AbstractFilter {

    private bool $asUl = false;
    private bool $asSeparated = true;
    private bool $asArray = true;
    private bool $indexedByRole = false;
    private string $separator = ', ';

    private bool $displayRole = false;
    private bool $displayRoleLibelle = false;
    private bool $displayRoleComplement = false;
    private bool $displayQualite = false;
    private bool $displayEtablissement = false;
    private bool $displayUniteRecherche = false;

    private $contrainteRole = null;
    private $contrainteRoleLibelle = null;
    private $contrainteRoleComplement = null;
    private $contrainteQualite = null;
    private $contrainteEtablissement = null;
    private $contrainteUniteRecherche = null;

    /**
     * Set the returned data type to unordered list
     */
    public function asUl(): static
    {
        $this->asUl = true;
        $this->asSeparated = false;
        $this->asArray = false;

        return $this;
    }

    /**
     * Set the returned data type to separated value format and set the separator
     */
    public function asSeparated(string $separator = ", "): static
    {
        $this->asUl = false;
        $this->asSeparated = true;
        $this->separator = $separator;
        $this->asArray = false;

        return $this;
    }

    /**
     * Set the returned data type to separated value format and set the separator
     */
    public function asArray(bool $indexedByRole = false): self
    {
        $this->asUl = false;
        $this->asSeparated = false;
        $this->asArray = true;
        $this->indexedByRole = $indexedByRole;

        return $this;
    }


    /**
     * Set the displayed keys
     *
     * @param array $params of keys [role, complement, qualite, etablissement] and boolean
     */
    public function paramDisplay(array $params): static
    {
        foreach ($params as $key => $value) {
            switch ($key) {
                case "role": $this->displayRole = $value;
                    break;
                case "roleLibelle": $this->displayRoleLibelle = $value;
                    break;
                case "complement": $this->displayRoleComplement = $value;
                    break;
                case "qualite": $this->displayQualite = $value;
                    break;
                case "etablissement": $this->displayEtablissement = $value;
                    break;
                case "uniteRecherche": $this->displayUniteRecherche = $value;
                    break;
            }
        }

        return $this;
    }

    /**
     * Format n array of acteurs
     *
     * @param Acteur[] $acteurs
     */
    public function doFormat(array $acteurs): array|string
    {
        $acteurs = $this->filter($acteurs);

        if ($this->asUl) {
            $acteurs = $this->doFormatUnorderedList($acteurs);
            $helper = new HtmlList();
            $result = $helper($acteurs, $ordered = false, $attribs = false, $escape = false);
        }
        elseif ($this->asSeparated) {
            $acteurs = $this->doFormatSeparated($acteurs);
//            $result = implode($this->separator, $acteurs);
            $result = $acteurs;
        }
        elseif ($this->asArray) {
            $result = $this->doFormatArray($acteurs);
        }
        else {
            throw new LogicException("Cas inattendu !");
        }

        return $result;
    }

    /**
     * This function format an array of acteurs as a unordered list
     *
     * @param Acteur[] $acteurs
     */
    private function doFormatUnorderedList(array $acteurs): string
    {
        $acteurs = array_map([$this, 'htmlifyActeur'], $acteurs);
        $helper = new HtmlList();

        return $helper($acteurs, $ordered = false, ['class' => 'row'], $escape = false);
    }

    /**
     * This function format an array of acteurs as Separated Values object
     *
     * @param  Acteur[] $acteurs
     * @return string Separated Values object
     */
    private function doFormatSeparated(array $acteurs): string
    {
        $acteurs = array_map([$this, 'htmlifyActeur'], $acteurs);

        return implode($this->separator, $acteurs);
    }

    /**
     * This function format an array of acteurs as Array.
     *
     * @param Acteur[] $acteurs
     * @return array Array of array with key => value
     */
    private function doFormatArray(array $acteurs): array
    {
        $results = [];

        foreach ($acteurs as $acteur) {
            $these = $acteur->getThese();
            $estModifiable = !$these->getSource()->getImportable();
            $qualite = $estModifiable ? $acteur->getQualite() : $acteur->getLibelleQualite();
            $result = [];
            $result["acteur"] = $acteur;
            $result["nom"] = $acteur->getIndividu()->getNomComplet(true);
            if ($this->displayRole === true) {
                $result["role"] = $acteur->getRole()->getRoleId();
            }
            if ($this->displayRoleComplement === true && trim($acteur->getLibelleRoleComplement())) {
                $result["complement"] = $acteur->getLibelleRoleComplement();
            }
            if ($this->displayQualite === true && trim($qualite)) {
                $result["qualite"] = $qualite;
            }
            if ($this->displayEtablissement === true) {
                $result["etablissement"] = ($etab = $acteur->getEtablissement()) ? $etab->getStructure()->getLibelle() : "(Établissement non renseigné)";
                $result["etablissementForce"] = ($etab = $acteur->getEtablissementForce()) ? $etab->getStructure()->getLibelle() : null;
            }
            if ($this->displayUniteRecherche === true && $acteur->getUniteRecherche()) {
                $result["uniteRecherche"] = $acteur->getUniteRecherche()->getStructure()->getLibelle();
            }
            if ($acteur->getIndividu()->getSupannId() === null) {
                $result['alerte-supann-id'] = sprintf(
                    "Cette personne ne pourra pas utiliser l'application car il manque des informations la concernant dans %s (source code '%s').",
                    $acteur->getIndividu()->getSource(),
                    $acteur->getIndividu()->getSourceCode());
            }

            if ($this->indexedByRole) {
                $results[$result["role"]] = $results[$result["role"]] ?? [];
                $results[$result["role"]][] = $result;
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    public function htmlifyActeur(Acteur $a): string
    {
        $str = (string)$a->getIndividu();

        if ($this->displayRole) {
            $str .= " <b>" . $a->getRole()->getRoleId() . "</b>";
        }
        if ($this->displayRoleComplement && $a->getLibelleRoleComplement() && trim($a->getLibelleRoleComplement())) {
            $str .= " (" . $a->getLibelleRoleComplement() . ")";
        }
        if ($this->displayQualite && $a->getQualite() && trim($a->getQualite())) {
            $str .= ", " . $a->getQualite();
        }
        if ($this->displayEtablissement) {
            $etab = $a->getEtablissementForce() ?: $a->getEtablissement();
            $str .= $etab ? ", " . $etab->getStructure()->getLibelle() : "Établissement non renseigné";
        }
        if ($this->displayUniteRecherche) {
            $str .= ($ur = $a->getUniteRecherche()) ? ", " . $ur->getStructure()->getLibelle() : "UR non renseignée";
        }

        return $str;
    }

    /**
     * Set the displayed keys
     *
     * @param array $params of keys [role, complement, qualite, etablissement] and boolean
     */
    public function paramFilter(array $params): static
    {
        foreach ($params as $key => $value) {
            switch ($key) {
                case "role": $this->contrainteRole = $value;
                    break;
                    case "roleLibelle": $this->contrainteRoleLibelle = $value;
                    break;
                case "complement": $this->contrainteRoleComplement = $value;
                    break;
                case "qualite": $this->contrainteQualite = $value;
                    break;
                case "etablissement": $this->contrainteEtablissement = $value;
                    break;
                case "uniteRecherche": $this->contrainteUniteRecherche = $value;
                    break;
            }
        }

        return $this;
    }

    public function filter($acteurs): array
    {
        $results = [];

        /** @var Acteur $acteur */
        foreach($acteurs as $acteur) {
            $etab = $acteur->getEtablissementForce() ?: $acteur->getEtablissement();
            $keep = true;
            if ($keep && $this->contrainteRole != null && !in_array($acteur->getRole()->getCode(), (array)$this->contrainteRole)) $keep = false;
            if ($keep && $this->contrainteRoleLibelle != null && $acteur->getRole()->getLibelle() != $this->contrainteRoleLibelle) $keep = false;
            if ($keep && $this->contrainteRoleComplement != null && $acteur->getLibelleRoleComplement() != $this->contrainteRoleComplement) $keep = false;
            if ($keep && $this->contrainteQualite != null && $acteur->getQualite() != $this->contrainteQualite) $keep = false;
            if ($keep && $this->contrainteEtablissement != null && (! $etab || $etab->getStructure()->getLibelle() != $this->contrainteEtablissement)) $keep = false;
            if ($keep && $this->contrainteUniteRecherche != null && (! ($ur = $acteur->getUniteRecherche()) || $ur->getStructure()->getLibelle() != $this->contrainteUniteRecherche)) $keep = false;
            if ($keep) $results[] = $acteur;
        }

        return $results;
    }

}