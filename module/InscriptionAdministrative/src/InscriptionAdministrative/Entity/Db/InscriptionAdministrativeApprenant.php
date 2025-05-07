<?php

namespace InscriptionAdministrative\Entity\Db;

use Application\Constants;
use Application\Entity\Db\Source;
use DateTime;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * @method Source getSource()
 */
class InscriptionAdministrativeApprenant implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    private int $id;
    private string $sourceCode;

    private string $code;
    private string $INE;
    private ?string $deuxiemePrenom = null;
    private string $genre;
    private ?string $mailPersonnel = null;
    private ?string $mailUrgence = null;
    private string $nomDeNaissance;
    private string $nomUsuel;
    private string $prenom;
    private ?string $prenomUsage = null;
    private ?string $telephonePersonnel = null;
    private ?string $telephoneUrgence = null;
    private ?string $troisiemePrenom = null;
    private ?string $communeDeNaissance = null;
    private ?string $communeDeNaissanceEtranger = null;
    private ?DateTime $dateDObtentionDeLaDeuxiemeNationalite = null;
    private DateTime $dateDeNaissance;
    private ?string $deuxiemeNationalite = null;
    private ?string $libelleCommuneDeNaissance = null;
    private ?string $libelleDeuxiemeNationalite = null;
    private ?string $libelleNationalite = null;
    private ?string $libellePaysDeNaissance = null;
    private string $nationalite;
    private string $paysDeNaissance;

    private string $haystack;

    private InscriptionAdministrative $inscriptionAdministrative;

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Retourne le code apprenant.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    public function getINE(): string
    {
        return $this->INE;
    }

    public function getDeuxiemePrenom(): ?string
    {
        return $this->deuxiemePrenom;
    }

    public function getGenre(): string
    {
        return $this->genre;
    }

    public function getMailPersonnel(): ?string
    {
        return $this->mailPersonnel;
    }

    public function getMailUrgence(): ?string
    {
        return $this->mailUrgence;
    }

    public function getNomDeNaissance(): string
    {
        return $this->nomDeNaissance;
    }

    public function getNomUsuel(): string
    {
        return $this->nomUsuel;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getPrenomUsage(): ?string
    {
        return $this->prenomUsage;
    }

    public function getTelephonePersonnel(): ?string
    {
        return $this->telephonePersonnel;
    }

    public function getTelephoneUrgence(): ?string
    {
        return $this->telephoneUrgence;
    }

    public function getTroisiemePrenom(): ?string
    {
        return $this->troisiemePrenom;
    }

    public function getCommuneDeNaissance(): ?string
    {
        return $this->communeDeNaissance;
    }

    public function getCommuneDeNaissanceEtranger(): ?string
    {
        return $this->communeDeNaissanceEtranger;
    }

    public function getDateDObtentionDeLaDeuxiemeNationalite(): ?DateTime
    {
        return $this->dateDObtentionDeLaDeuxiemeNationalite;
    }

    public function getDateDObtentionDeLaDeuxiemeNationaliteToString(): ?string
    {
        return $this->dateDObtentionDeLaDeuxiemeNationalite?->format(Constants::DATE_FORMAT);
    }

    public function getDateDeNaissance(): DateTime
    {
        return $this->dateDeNaissance;
    }

    public function getDateDeNaissanceToString(): string
    {
        return $this->dateDeNaissance->format(Constants::DATE_FORMAT);
    }

    public function getDeuxiemeNationalite(): ?string
    {
        return $this->deuxiemeNationalite;
    }

    public function getLibelleCommuneDeNaissance(): ?string
    {
        return $this->libelleCommuneDeNaissance;
    }

    public function getLibelleDeuxiemeNationalite(): ?string
    {
        return $this->libelleDeuxiemeNationalite;
    }

    public function getLibelleNationalite(): ?string
    {
        return $this->libelleNationalite;
    }

    public function getLibellePaysDeNaissance(): ?string
    {
        return $this->libellePaysDeNaissance;
    }

    public function getNationalite(): string
    {
        return $this->nationalite;
    }

    /**
     * Retourne le code pays du pays de naissance (code ISO-alpha 2).
     */
    public function getPaysDeNaissance(): string
    {
        return $this->paysDeNaissance;
    }

    public function getInscriptionAdministrative(): InscriptionAdministrative
    {
        return $this->inscriptionAdministrative;
    }

    public function getHaystack(): string
    {
        return $this->haystack;
    }
}