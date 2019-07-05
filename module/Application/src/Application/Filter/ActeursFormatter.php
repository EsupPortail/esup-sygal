<?php

namespace Application\Filter;

use Application\Entity\Db\Acteur;
use Zend\View\Helper\HtmlList;
use Zend\Filter\AbstractFilter;
use Zend\View\Renderer\PhpRenderer;

//TODO JP 22/11/2017 doFormat need to be able to use Collection, These and array of Acteur
// add the transformation in doFormat

/** --- Class ActeursFormatteur ---
 * @var bool $asUl                      returned data type as unordered list (html)
 * @var bool $asSeparated               returned data type as separated value
 * @var bool $asArray                   returned data type as array
 * @var string $separator               separator used in the separated value format
 */
class ActeursFormatter extends AbstractFilter {

    /** @var bool */
    private $asUl = false;
    /** @var bool */
    private $asSeparated = true;
    /** @var bool */
    private $asArray = true;
    /** @var string */
    private $separator;

    private $displayRole = false;
    private $displayRoleLibelle = false;
    private $displayRoleComplement = false;
    private $displayQualite = false;
    private $displayEtablissement = false;

    private $contrainteRole = null;
    private $contrainteRoleLibelle = null;
    private $contrainteRoleComplement = null;
    private $contrainteQualite = null;
    private $contrainteEtablissement = null;

    /** Set the returned data type to unordered list
     *  @return $this
     */
    public function asUl()
    {
        $this->asUl = true;
        $this->asSeparated = false;
        $this->asArray = false;

        return $this;
    }

    /** Set the returned data type to separated value format and set the separator
     * @param string $separator (default = ", ")
     * @return $this
     */
    public function asSeparated($separator = ", ")
    {
        $this->asUl = false;
        $this->asSeparated = true;
        $this->separator = $separator;
        $this->asArray = false;

        return $this;
    }

    /** Set the returned data type to separated value format and set the separator
     * @param string $separator (default = ", ")
     * @return $this
     */
    public function asArray()
    {
        $this->asUl = false;
        $this->asSeparated = false;
        $this->asArray = true;

        return $this;
    }


    /** Set the displayed keys
     * @param array $params of keys [role, complement, qualite, etablissement] and boolean
     * @return $this
     */
    public function paramDisplay(array $params) {
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
            }
        }

        return $this;
    }

    /** Format n array of acteurs
     * @param Acteur[] $acteurs
     * @return formated set of acteurs
     */
    public function doFormat($acteurs)
    {
        if ($this->asUl) {
            $acteurs = $this->doFormatUnorderedList($acteurs);
            $helper = new HtmlList();
            $helper->setView(new PhpRenderer());
            $result = $helper($acteurs, $ordered = false, $attribs = false, $escape = false);
        }
        elseif ($this->asSeparated) {
            $acteurs = $this->doFormatSeparated($acteurs);
//            $result = implode($this->separator, $acteurs);
            $result = $acteurs;
        }
        elseif ($this->asArray()) {
            $result = $this->doFormatArray($acteurs);
        }
        else {
            throw new \LogicException("Cas inattendu !");
        }

        return $result;
    }

    /** This function format an array of acteurs as a unordered list
     * @param Acteur[] $acteurs
     * @return an unordered list
     */
    private function doFormatUnorderedList($acteurs) {
        $acteurs = array_map([$this, 'htmlifyActeur'], $acteurs);
        $helper = new HtmlList();
        $helper->setView(new PhpRenderer());
        $results = $helper($acteurs, $ordered = false, $attribs = false, $escape = false);
        return $results;
    }

    /** This function format an array of acteurs as Separated Values object
     * @param  Acteur[] $acteurs
     * @return Separated Values object
     */
    private function doFormatSeparated($acteurs) {
        $acteurs = array_map([$this, 'htmlifyActeur'], $acteurs);
        $results = implode($this->separator, $acteurs);
        return $results;
    }

    /**
     * This function format an array of acteurs as Array.
     *
     * @param Acteur[] $acteurs
     * @return array Array of array with key => value
     */
    private function doFormatArray($acteurs) {
        $results = [];
        /** @var Acteur $acteur */
        foreach ($acteurs as $acteur) {
            $result = [];
            $result["acteur"] = $acteur;
            $result["nom"] = $acteur->getIndividu()->getNomComplet(true);
            if ($this->displayRole === true) {
                $result["role"] = $acteur->getRole()->getRoleId();
            }
            if ($this->displayRoleComplement === true) {
                $result["complement"] = $acteur->getLibelleRoleComplement();
            }
            if ($this->displayQualite === true) {
                $result["qualite"] = $acteur->getQualite();
            }
            if ($this->displayEtablissement === true) {
                $result["etablissement"] = ($etab = $acteur->getEtablissement()) ? $etab->getStructure()->getLibelle() : "(Établissement non renseigné)";
            }
            if ($acteur->getIndividu()->getSupannId() === null) {
                $result['alerte-supann-id'] = sprintf(
                    "Cette personne ne pourra pas utiliser l'application car il manque des informations la concernant dans %s (source code '%s').",
                    $acteur->getIndividu()->getSource(),
                    $acteur->getIndividu()->getSourceCode());
            }
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @param Acteur $a
     * @return string HTML
     */
    public function htmlifyActeur(Acteur $a)
    {
        $str = (string) $a->getIndividu();
        if ($this->displayRole)           $str .= " <b>".$a->getRole()->getRoleId()."</b>";
        if ($this->displayRoleComplement) $str .= " (".$a->getLibelleRoleComplement().")";
        if ($this->displayQualite) $str .= ", ".$a->getQualite();
        if ($this->displayRoleComplement) $str .= (($etab = $a->getEtablissement()) ? ", " . $etab->getStructure()->getLibelle() : "Établissement non renseigné");
        return $str;
    }

    /** Set the displayed keys
     * @param Array $params of keys [role, complement, qualite, etablissement] and boolean
     */
    public function paramFilter(array $params) {
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
            }
        }

        return $this;
    }


    public function filter($acteurs) {

        $results = [];

        /** @var Acteur $acteur */
        foreach($acteurs as $acteur) {

            $keep = true;
            if ($keep && $this->contrainteRole != null && $acteur->getRole()->getCode() != $this->contrainteRole) $keep = false;
            if ($keep && $this->contrainteRoleLibelle != null && $acteur->getRole()->getLibelle() != $this->contrainteRoleLibelle) $keep = false;
            if ($keep && $this->contrainteRoleComplement != null && $acteur->getLibelleRoleComplement() != $this->contrainteRoleComplement) $keep = false;
            if ($keep && $this->contrainteQualite != null && $acteur->getQualite() != $this->contrainteQualite) $keep = false;
            if ($keep && $this->contrainteEtablissement != null && (! $acteur->getEtablissement() || $acteur->getEtablissement()->getStructure()->getLibelle() != $this->contrainteEtablissement)) $keep = false;
            if ($keep) $results[] = $acteur;
        }

        return $results;
    }

}