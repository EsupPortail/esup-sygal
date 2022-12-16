<?php

namespace Doctorant\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Doctorant
 */
class Doctorant implements HistoriqueAwareInterface, ResourceInterface, IndividuAwareInterface
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

    protected ?string $codeApprenantInSource = null;

    /**
     * @var Individu
     */
    private $individu;

    /**
     * @var Collection
     */
    private $theses;

    /**
     * @var Etablissement|null
     */
    protected ?Etablissement $etablissement = null;

    /**
     * @var string
     */
    private $ine;

    private Collection $missionsEnseignements;


    /**
     * Retourne l'éventuel établissement lié *ou son substitut le cas échéant*.
     *
     * **ATTENTION** : veiller à bien faire les jointures suivantes en amont avant d'utiliser cet accesseur :
     * '.etablissement' puis 'etablissement.structure' puis 'structure.structureSubstituante' puis 'structureSubstituante.etablissement'.
     *
     * @param bool $returnSubstitIfExists À true, retourne l'établissement substituant s'il y en a un ; sinon l'établissement d'origine.
     * @return Etablissement|null
     * @see Etablissement::getEtablissementSubstituant()
     */
    public function getEtablissement(bool $returnSubstitIfExists = true): ?Etablissement
    {
        if ($returnSubstitIfExists && $this->etablissement && ($sustitut = $this->etablissement->getEtablissementSubstituant())) {
            return $sustitut;
        }

        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->theses = new ArrayCollection();
        $this->missionsEnseignements = new ArrayCollection();
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

    public function getCodeApprenantInSource(): ?string
    {
        return $this->codeApprenantInSource;
    }

    public function setCodeApprenantInSource(?string $codeApprenantInSource): self
    {
        $this->codeApprenantInSource = $codeApprenantInSource;
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
     * Get numeroEtudiant
     *
     * @return string
     */
    public function getNumeroEtudiant()
    {
        return $this->getIndividu()->getSupannId(); // todo: à remplacer par $this->>numeroEtudiant lorsqu'il sera importé.
    }

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->getIndividu()->setDateNaissance($dateNaissance);

        return $this;
    }

    /**
     * Get dateNaissance
     *
     * @return \DateTime
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getDateNaissance()
    {
        return $this->getIndividu()->getDateNaissance();
    }

    /**
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNationalite()
    {
        return $this->getIndividu()->getNationalite();
    }

    /**
     * @param string $nationalite
     * @return Doctorant
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setNationalite($nationalite)
    {
        $this->getIndividu()->setNationalite($nationalite);

        return $this;
    }

    /**
     * Set nomPatronymique
     *
     * @param string $nomPatronymique
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setNomPatronymique($nomPatronymique)
    {
        $this->getIndividu()->setNomPatronymique($nomPatronymique);

        return $this;
    }

    /**
     * Get nomPatronymique
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNomPatronymique()
    {
        return $this->getIndividu()->getNomPatronymique();
    }

    /**
     * Set nomUsuel
     *
     * @param string $nomUsuel
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setNomUsuel($nomUsuel)
    {
        $this->getIndividu()->setNomUsuel($nomUsuel);

        return $this;
    }

    /**
     * Get nomUsuel
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNomUsuel()
    {
        return $this->getIndividu()->getNomUsuel();
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setPrenom($prenom)
    {
        $this->getIndividu()->setPrenom($prenom);

        return $this;
    }

    /**
     * Get prenom
     *
     * @param bool $tous
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getPrenom($tous = false)
    {
        return $tous ? $this->getIndividu()->getPrenoms() : $this->getIndividu()->getPrenom();
    }

    /**
     * Get prenoms
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getPrenoms()
    {
        return $this->getIndividu()->getPrenoms();
    }

    /**
     * Set civilite
     *
     * @param string $civilite
     *
     * @return self
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function setCivilite($civilite)
    {
        $this->getIndividu()->setCivilite($civilite);

        return $this;
    }

    /**
     * Get civilite
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getCivilite()
    {
        return $this->getIndividu()->getCivilite();
    }

    /**
     * Get civilite
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getCiviliteToString()
    {
        return $this->getIndividu()->getCiviliteToString();
    }

    /**
     * Get estUneFemme
     *
     * @return bool
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function estUneFemme()
    {
        return $this->getIndividu()->estUneFemme();
    }

    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getIndividu()->__toString();
    }

    /**
     * Get nomUsuel
     *
     * @param bool $avecCivilite
     * @param bool $avecNomPatro
     * @param bool $prenoms
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getNomComplet($avecCivilite = false, $avecNomPatro = false, $prenoms = false)
    {
        return $this->getIndividu()->getNomComplet($avecCivilite, $avecNomPatro, $prenoms);
    }

    /**
     * Get dateNaissance
     *
     * @return string
     * @deprecated Passe par getIndividu() toi-même !
     */
    public function getDateNaissanceToString()
    {
        return $this->getIndividu()->getDateNaissanceToString();
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

    /**
     * @return \These\Entity\Db\These[]
     */
    public function getTheses()
    {
        return $this->theses->toArray();
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     * @see ResourceInterface
     */
    public function getResourceId()
    {
        return 'Doctorant';
    }

    public function getIne(): string
    {
        return $this->ine;
    }

    public function setIne(string $ine): Doctorant
    {
        $this->ine = $ine;
        return $this;
    }

    /** @return MissionEnseignement[] */
    public function getMissionsEnseignements(): array
    {
        return $this->missionsEnseignements->toArray();
    }

    public function hasMissionEnseignementFor(int $annee): bool
    {
        foreach ($this->getMissionsEnseignements() as $missionEnseignement) {
            if ($missionEnseignement->getAnneeUniversitaire() === $annee) return true;
        }
        return false;
    }

}
