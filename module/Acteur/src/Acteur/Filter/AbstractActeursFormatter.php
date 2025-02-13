<?php

namespace Acteur\Filter;

use Acteur\Entity\Db\AbstractActeur;
use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Laminas\Filter\AbstractFilter;
use Laminas\View\Helper\HtmlList;
use LogicException;
use Structure\Entity\Db\Etablissement;

/**
 * @var bool $asUl                      returned data type as unordered list (html)
 * @var bool $asSeparated               returned data type as separated value
 * @var bool $asArray                   returned data type as array
 * @var string $separator               separator used in the separated value format
 */
abstract class AbstractActeursFormatter extends AbstractFilter
{
    protected bool $asUl = false;
    protected bool $asSeparated = true;
    protected bool $asArray = true;
    protected bool $indexedByRole = false;
    protected string $separator = ', ';

    protected bool $displayRole = false;
    protected bool $displayRoleLibelle = false;
    protected bool $displayQualite = false;
    protected bool $displayEtablissement = false;
    protected bool $displayUniteRecherche = false;

    protected $contrainteRole = null;
    protected $contrainteRoleLibelle = null;
    protected $contrainteQualite = null;
    protected $contrainteEtablissement = null;
    protected $contrainteUniteRecherche = null;

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
     * @param ActeurThese[]|ActeurHDR[] $acteurs
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
     * @param ActeurThese[]|ActeurHDR[] $acteurs
     */
    protected function doFormatUnorderedList(array $acteurs): string
    {
        $acteurs = array_map([$this, 'htmlifyActeur'], $acteurs);
        $helper = new HtmlList();

        return $helper($acteurs, $ordered = false, ['class' => 'row'], $escape = false);
    }

    /**
     * This function format an array of acteurs as Separated Values object
     *
     * @param  ActeurThese[]|ActeurHDR[] $acteurs
     * @return string Separated Values object
     */
    protected function doFormatSeparated(array $acteurs): string
    {
        $acteurs = array_map([$this, 'htmlifyActeur'], $acteurs);

        return implode($this->separator, $acteurs);
    }

    /**
     * This function format an array of acteurs as Array.
     *
     * @param ActeurThese[]|ActeurHDR[] $acteurs
     * @return array Array of array with key => value
     */
    protected function doFormatArray(array $acteurs): array
    {
        $results = [];

        foreach ($acteurs as $acteur) {
            $result = $this->doFormatArrayActeur($acteur);

            if ($this->indexedByRole && $this->displayRole === true) {
                $results[$result["role"]] = $results[$result["role"]] ?? [];
                $results[$result["role"]][] = $result;
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    protected function doFormatArrayActeur(AbstractActeur $acteur): array
    {
        $result = [];

        $result["acteur"] = $acteur;
        $result["nom"] = $acteur->getIndividu()->getNomCompletFormatter()->avecCivilite()->f();
        if ($this->displayRole === true) {
            $result["role"] = $acteur->getRole()->getRoleId();
        }
        if ($this->displayQualite === true && trim($acteur->getLibelleQualite())) {
            $result["qualite"] = $acteur->getLibelleQualite();
        }
        if ($this->displayEtablissement === true) {
            $result["etablissement"] = ($etab = $acteur->getEtablissement()) ? $etab->getStructure()->getLibelle() : "(Établissement non renseigné)";
//            $result["etablissementForce"] = ($etab = $acteur->getEtablissementForce()) ? $etab->getStructure()->getLibelle() : null;
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

        return $result;
    }

    public function htmlifyActeur(AbstractActeur $a): string
    {
        $str = (string)$a->getIndividu();

        if ($this->displayRole) {
            $str .= " <b>" . $a->getRole()->getRoleId() . "</b>";
        }
        if ($this->displayQualite && $a->getLibelleQualite() && trim($a->getLibelleQualite())) {
            $str .= ", " . $a->getLibelleQualite();
        }
        if ($this->displayEtablissement) {
            $etab = $this->getEtablissementActeur($a);
            $str .= $etab ? ", " . $etab->getStructure()->getLibelle() : "Établissement non renseigné";
        }
        if ($this->displayUniteRecherche) {
            $str .= ($ur = $a->getUniteRecherche()) ? ", " . $ur->getStructure()->getLibelle() : "UR non renseignée";
        }

        return $str;
    }

    protected function getEtablissementActeur(AbstractActeur $acteur): ?Etablissement
    {
        return $acteur->getEtablissement();
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

    /**
     * @param AbstractActeur[] $acteurs
     * @return array
     */
    public function filter($acteurs): array
    {
        $results = [];

        foreach($acteurs as $acteur) {
            $etab = $this->getEtablissementActeur($acteur);
            $keep = true;
            if ($keep && $this->contrainteRole != null && !in_array($acteur->getRole()->getCode(), (array)$this->contrainteRole)) $keep = false;
            if ($keep && $this->contrainteRoleLibelle != null && $acteur->getRole()->getLibelle() != $this->contrainteRoleLibelle) $keep = false;
            if ($keep && $this->contrainteQualite != null && $acteur->getLibelleQualite() != $this->contrainteQualite) $keep = false;
            if ($keep && $this->contrainteEtablissement != null && (! $etab || $etab->getStructure()->getLibelle() != $this->contrainteEtablissement)) $keep = false;
            if ($keep && $this->contrainteUniteRecherche != null && (! ($ur = $acteur->getUniteRecherche()) || $ur->getStructure()->getLibelle() != $this->contrainteUniteRecherche)) $keep = false;
            if ($keep) $results[] = $acteur;
        }

        return $results;
    }
}