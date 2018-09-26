<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * --- Class Acteur ---
 *
 * @var integer  $id                    un identifiant unique servant de clef
 * @var string   $sourceCode            liens vers apogée
 * @var string   $libelleRoleComplement Complément de rôle (p.e. Co-encadrement, Président de Jury ...)
 * @var These    $these                 Information sur l'etat de la thèse et des acteurs de la thèse
 * @var Individu $individu              Information sur la personne (p.e. Nom, Mail, ...)
 * @var Role     $role                  Role de l'acteur (p.e. directeur de thèse)
 * @var string   $qualite               la qualité de l'acteur (p.e. chargé de recherche, ...)
 * @var string   $etalissement          l'étabilissement d'attachement de l'acteur (p.e. Université de Caen Normandie,
 *      ...)
 */
class Acteur implements HistoriqueAwareInterface, ResourceInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $libelleRoleComplement;

    /**
     * @var These
     */
    private $these;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * @var Role
     */
    private $role;
    /** @var string $qualite */
    private $qualite;

    /**
     * Etablissement auquel appartient l'individu.
     *
     * @var Etablissement
     */
    private $etablissement;

    /**
     * Fonction de callback à utiliser pour trier une collection d'acteurs
     * selon leur rôle.
     *
     * @param Acteur $a1
     * @param Acteur $a2
     * @return int
     */
    static public function cmp(Acteur $a1, Acteur $a2)
    {
        $ordres = Role::$ordreSourcesCodes;
        $ordre1 = array_key_exists($a1->getRole()->getSourceCode(), $ordres) ? $ordres[$a1->getRole()->getSourceCode()] : 'zzz';
        $ordre2 = array_key_exists($a2->getRole()->getSourceCode(), $ordres) ? $ordres[$a2->getRole()->getSourceCode()] : 'zzz';

        $nom1 = ($i1 = $a1->getIndividu()) ? $i1->getNomUsuel() . $i1->getPrenom() : '';
        $nom2 = ($i2 = $a2->getIndividu()) ? $i2->getNomUsuel() . $i2->getPrenom() : '';

        $x = $ordre1 . $nom1;
        $y = $ordre2 . $nom2;

        if ($x === $y) {
            return 0;
        }

        return strcmp($x, $y);
    }

    /**
     * Prédicat testant si cet acteur est un directeur OU co-directeur de thèse.
     *
     * @return bool
     */
    public function estDirecteur()
    {
        return in_array($this->getRole()->getCode(), [
            Role::CODE_DIRECTEUR_THESE,
            Role::CODE_CODIRECTEUR_THESE]
        );
    }

    /**
     * Prédicat testant cet un acteur est un rapporteur de thèse.
     *
     * @return bool
     */
    public function estRapporteur()
    {
        return $this->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY;
    }


    /**
     * Prédicat testant cet un acteur est un rapporteur de thèse.
     *
     * @return bool
     */
    public function estPresidentJury()
    {
        return $this->getRole()->getCode() === Role::CODE_PRESIDENT_JURY;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getIndividu();
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
     *
     * @return self
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode
     *
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     */
    public function getLibelleRoleComplement()
    {
        return $this->libelleRoleComplement;
    }

    /**
     * @param string $libelleRoleComplement
     * @return Acteur
     */
    public function setLibelleRoleComplement($libelleRoleComplement)
    {
        $this->libelleRoleComplement = $libelleRoleComplement;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return self
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * @return Individu
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return self
     */
    public function setIndividu($individu)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }


    /**
     * @param string $qualite
     * @return self
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;

        return $this;
    }

    /**
     * @return string qualite
     */
    public function getQualite()
    {
        if ($this->qualite === null) {
            return " ";
//            return "Qualité non indiquée";
        } else {
            return $this->qualite;
        }
    }

    /**
     * @param Etablissement|null $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Retourne l'établissement ÉVENTUEL auquel appartient l'individu.
     *
     * @return Etablissement|null
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param Role $role
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     * @see ResourceInterface
     */
    public function getResourceId()
    {
        return 'Acteur';
    }
}
