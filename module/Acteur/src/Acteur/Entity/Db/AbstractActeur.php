<?php

namespace Acteur\Entity\Db;

use Application\Entity\Db\Role;
use Closure;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRoleAwareInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Qualite;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

abstract class AbstractActeur implements HistoriqueAwareInterface, ResourceInterface, IndividuRoleAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var Individu
     */
    protected $individu;

    /**
     * @var Role
     */
    protected $role;

    protected ?Membre $membre = null;

    protected bool $exterieur = false;
    protected int $ordre = 1;

    /**
     * Etablissement auquel appartient l'individu.
     */
    protected ?Etablissement $etablissement = null;
//    protected ?Etablissement $etablissementForce = null;

    /**
     * Ecole Doctorale à laquelle appartient l'individu.
     */
    protected ?EcoleDoctorale $ecoleDoctorale = null;

    /**
     * Unité de recherche à laquelle appartient l'individu.
     */
    protected ?UniteRecherche $uniteRecherche = null;
    protected ?Qualite $qualite = null;

    /**
     * Retourne la fonction de callback à utiliser pour trier une collection d'entités Acteur selon leur rôle.
     * @see usort()
     *
     * @return callable
     */
    static public function getComparisonFunction()
    {
        return function(AbstractActeur $a1, AbstractActeur $a2) {
            return strcmp(
                $a1->getRole()->getOrdreAffichage() . $a1->getIndividu()->getNomUsuel() . $a1->getIndividu()->getPrenom(),
                $a2->getRole()->getOrdreAffichage() . $a2->getIndividu()->getNomUsuel() . $a2->getIndividu()->getPrenom()
            );
        };
    }


    /**
     * Retourne la fonction de callback à utiliser pour trier une collection d'entités Acteur selon leur rôle.
     * @see usort()
     *
     * @return callable
     */
    static public function getRoleComparisonFunction()
    {
        return function(AbstractActeur $a1, AbstractActeur $a2) {
            return strcmp(
                $a1->getRole()->getOrdreAffichage() . $a1->getIndividu()->getNomUsuel() . $a1->getIndividu()->getPrenom(),
                $a2->getRole()->getOrdreAffichage() . $a2->getIndividu()->getNomUsuel() . $a2->getIndividu()->getPrenom()
            );
        };
    }

    /**
     * Retourne la fonction de callback à utiliser pour trier une collection d'entités Acteur selon leur ordre.
     * @see usort()
     *
     * @return callable
     */
    static public function getOrdreComparisonFunction()
    {
        return fn(AbstractActeur $a1, AbstractActeur $a2) => $a1->getOrdre() <=> $a2->getOrdre();
    }

    /**
     * Retourne la fonction permettent de filtrer une collection d'acteurs selon qu'ils correspondent
     * au(x) rôle(s) spécifié(s).
     *
     * @param string|string[] $code
     */
    static public function getRoleFilterFunction($code): Closure
    {
        return function(AbstractActeur $a) use ($code) {
            return in_array($a->getRole()->getCode(), (array) $code);
        };
    }

    /**
     * Prédicat testant si cet acteur est un directeur, co-directeur de thèse ou garant dans le cas d'une HDR.
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
        return $this->role->isCoEncadrant();
    }

    public function estGarant() : bool
    {
        return in_array($this->getRole()->getCode(), [
                Role::CODE_HDR_GARANT,
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    public function setIndividu(?Individu $individu = null): self
    {
        $this->individu = $individu;
        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }


    /**
     * @param string|null $qualite
     * @return self
     */
    public function setLibelleQualite(?string $qualite): self
    {
        $this->qualite = $qualite;

        return $this;
    }

    /**
     * @return string qualite
     */
    public function getLibelleQualite(): string
    {
        $estImportable = $this->getSource()->getImportable();
        if($estImportable){
            return $this->libelleQualite === null ? " " : $this->libelleQualite;
        }else{
            return $this->qualite ?: "";
        }
    }

    /**
     * @return bool
     */
    public function isPrincipal(): bool
    {
            return $this->principal;
    }

    /**
     * Get principal.
     *
     * @return bool
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * @param bool $principal
     * @return self
     */
    public function setPrincipal(bool $principal = true): self
    {
        $this->principal = $principal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExterieur(): bool
    {
        return $this->exterieur;
    }

    /**
     * Get exterieur.
     *
     * @return bool
     */
    public function getExterieur()
    {
        return $this->exterieur;
    }

    /**
     * @param bool $exterieur
     * @return self
     */
    public function setExterieur(bool $exterieur = true): self
    {
        $this->exterieur = $exterieur;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrdre(): int
    {
        return $this->ordre;
    }

    /**
     * @param int $ordre
     * @return self
     */
    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement = null): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * Retourne l'éventuel établissement lié.
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param EcoleDoctorale|null $ecoleDoctorale
     * @return self
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale = null): self
    {
        $this->ecoleDoctorale = $ecoleDoctorale;

        return $this;
    }

    /**
     * Retourne l'éventuelle ED liée.
     */
    public function getEcoleDoctorale(): ?EcoleDoctorale
    {
        return $this->ecoleDoctorale;
    }

    /**
     * Retourne l'éventuelle UR liée.
     */
    public function getUniteRecherche(): ?UniteRecherche
    {
        return $this->uniteRecherche;
    }

    public function setUniteRecherche(?UniteRecherche $uniteRecherche): self
    {
        $this->uniteRecherche = $uniteRecherche;
        return $this;
    }

    /**
     * Retourne l'éventuelle qualité liée.
     */
    public function getQualite(): ?Qualite
    {

        return $this->qualite;
    }

    public function setQualite(?Qualite $qualite): self
    {
        $this->qualite = $qualite;
        return $this;
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
     * @return string
     */
    public function getGenreFromIndividu(): string
    {
        $individu = $this->getIndividu();
        if($individu && $individu->getCivilite()){
            return  $individu->estUneFemme() ? "F" : "H";
        }
        return "";
    }

    public function getDenomination(): string
    {
        return $this->getIndividu()->getNomComplet();
    }

    /**
     * Retourne l'adresse mail de cet acteur de thèse.
     *
     * @param bool $tryMembre Faut-il en dernier ressort retourner l'email de l'eventuel Membre lié ?
     * @return string|null
     */
    public function getEmail(bool $tryMembre = false): ?string
    {
        return $this->getIndividu()->getEmailPro() ?:
            $this->getIndividu()->getEmailUtilisateur() ?:
                ($tryMembre ? $this->getMembre()?->getEmail() : null);
    }
}
