<?php

namespace Doctorant\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Structure\Entity\Db\Etablissement;
use Substitution\Entity\Db\SubstitutionAwareEntityInterface;
use Substitution\Entity\Db\SubstitutionAwareEntityTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Doctorant implements
    HistoriqueAwareInterface,
    ResourceInterface,
    IndividuAwareInterface,
    SubstitutionAwareEntityInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;
    use SubstitutionAwareEntityTrait;

    private ?int $id = null;
    private ?string $sourceCode = null;
    private ?string $ine = null;
    private ?string $codeApprenantInSource = null;

    private ?Individu $individu = null;
    private ?Etablissement $etablissement = null;

    private Collection $theses;
    private Collection $missionsEnseignements;

    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    public function __construct()
    {
        $this->theses = new ArrayCollection();
        $this->missionsEnseignements = new ArrayCollection();
        $this->substitues = new ArrayCollection();
    }

    public function setSourceCode($sourceCode): static
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    public function getSourceCode(): ?string
    {
        return $this->sourceCode;
    }

    public function getCodeApprenantInSource(): ?string
    {
        return $this->codeApprenantInSource;
    }

    public function setCodeApprenantInSource(?string $codeApprenantInSource): static
    {
        $this->codeApprenantInSource = $codeApprenantInSource;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroEtudiant(): ?string
    {
        return $this->getIndividu()->getSupannId(); // todo: à remplacer par $this->>numeroEtudiant lorsqu'il sera importé.
    }

    public function getIne(): ?string
    {
        return $this->ine;
    }

    public function setIne(string $ine): static
    {
        $this->ine = $ine;
        return $this;
    }

    /**
     * Retourne le nom complet *au format par défaut (recommandé)* de l'individu lié.
     *
     * @see Individu::__toString()
     */
    public function __toString(): string
    {
        return $this->getIndividu()->getNomCompletFormatter()->avecNomPatronymique()->f();
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
    public function getTheses(): array
    {
        return $this->theses->toArray();
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


    /**
     * Returns the string identifier of the Resource
     */
    public function getResourceId(): string
    {
        return 'Doctorant';
    }

    public function getTypeSubstitution(): string
    {
        return 'doctorant';
    }


    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     * @deprecated À supprimer car remplacée par {@see \Doctorant\Renderer\DoctorantTemplateVariable::getDenominationPatronymique()}
     */
    public function getDenominationPatronymique(): string
    {
        return $this->getIndividu()->getNomCompletFormatter()->avecCivilite()->avecNomPatronymique()->f();
    }
}
