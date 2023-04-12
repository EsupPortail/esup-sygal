<?php

namespace These\Entity\Db;

use Application\Entity\Db\Role;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\Membre;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

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
 * @var string   $uniteRecherche        l'unité de recherche d'attachement de l'acteur (p.e. GREYC,
 *      ...)
 */
class Acteur implements HistoriqueAwareInterface, ResourceInterface, IndividuAwareInterface
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
     * @var Membre
     */
    private $membre;

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
     * Etablissement auquel appartient l'individu.
     *
     * @var UniteRecherche
     */
    private $uniteRecherche;

    /**
     * Retourne la fonction de callback à utiliser pour trier une collection d'entités Acteur selon leur rôle.
     * @see usort()
     *
     * @return callable
     */
    static public function getComparisonFunction()
    {
        return function(Acteur $a1, Acteur $a2) {
            return strcmp(
                $a1->getRole()->getOrdreAffichage() . $a1->getIndividu()->getNomUsuel() . $a1->getIndividu()->getPrenom(),
                $a2->getRole()->getOrdreAffichage() . $a2->getIndividu()->getNomUsuel() . $a2->getIndividu()->getPrenom()
            );
        };
    }

    /**
     * Prédicat testant si cet acteur est un directeur OU co-directeur de thèse.
     *
     * @return bool
     */
    public function estDirecteur() : bool
    {
        return in_array($this->getRole()->getCode(), [
                Role::CODE_DIRECTEUR_THESE,
            ]
        );
    }
    public function estCodirecteur() : bool
    {
        return in_array($this->getRole()->getCode(), [
                Role::CODE_CODIRECTEUR_THESE,
            ]
        );
    }
    public function estCoEncadrant() : bool
    {
        return in_array($this->getRole()->getCode(), [
                Role::CODE_CO_ENCADRANT,
            ]
        );
    }

    /**
     * Prédicat testant cet un acteur est un rapporteur de thèse.
     *
     * @return bool
     */
    public function estRapporteur() : bool
    {
        return $this->getRole()->getCode() === Role::CODE_RAPPORTEUR_JURY;
    }

    /**
     * Prédicat testant cet un acteur est un rapporteur absent de thèse.
     *
     * @return bool
     */
    public function estRapporteurAbsent() : bool
    {
        return $this->getRole()->getCode() === Role::CODE_RAPPORTEUR_ABSENT;
    }

    /**
     * Prédicat testant cet un acteur est un rapporteur de thèse.
     *
     * @return bool
     */
    public function estPresidentJury() : bool
    {
        return ($this->getRole()->getCode() === Role::CODE_PRESIDENT_JURY);
    }

    /**
     * Prédicat testant cet un acteur est un membre du jury de thèse.
     *
     * @return bool
     */
    public function estMembreDuJury() : bool
    {
        return $this->getRole()->getCode() === Role::CODE_MEMBRE_JURY;
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
    public function setIndividu(Individu $individu = null)
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
     * Retourne l'éventuel établissement lié *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.etablissement' puis 'etablissement.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.etablissement'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'établissement substituant s'il y en a un ; sinon l'établissement d'origine.
     * @see Etablissement::getEtablissementSubstituant()
     * @return Etablissement|null
     */
    public function getEtablissement(bool $returnSubstitIfExists = true): ?Etablissement
    {
        if ($returnSubstitIfExists && $this->etablissement && ($sustitut = $this->etablissement->getEtablissementSubstituant())) {
            return $sustitut;
        }

        return $this->etablissement;
    }

    /**
     * Retourne l'éventuelle UR liée *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.uniteRecherche' puis 'uniteRecherche.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.uniteRecherche'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'UR substituante s'il y en a une ; sinon l'UR d'origine.
     * @see UniteRecherche::getUniteRechercheSubstituante()
     * @return UniteRecherche|null
     */
    public function getUniteRecherche(bool $returnSubstitIfExists = true): ?UniteRecherche
    {
        if ($returnSubstitIfExists && $this->uniteRecherche && ($sustitut = $this->uniteRecherche->getUniteRechercheSubstituante())) {
            return $sustitut;
        }

        return $this->uniteRecherche;
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

    /**
     * @return Membre|null
     */
    public function getMembre(): ?Membre
    {
        return $this->membre;
    }
}
