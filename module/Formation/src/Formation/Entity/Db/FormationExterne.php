<?php

namespace Formation\Entity\Db;

use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Traits\HasTypeTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationFormationExterne;

class FormationExterne implements HistoriqueAwareInterface, HasTypeInterface, ResourceInterface {
    use HistoriqueAwareTrait;
    use HasTypeTrait;

    private ?int $id = null;
    private ?string $libelle = null;
    private ?DateTime $debut = null;
    private ?DateTime $fin = null;
    private ?int $duree = null;
    private ?Doctorant $doctorant = null;
    private ?Fichier $attestation = null;
    private $validations;

    public function __construct()
    {
        $this->validations = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     * @return Formation
     */
    public function setLibelle(?string $libelle): FormationExterne
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return Fichier|null
     */
    public function getAttestation(): ?Fichier
    {
        return $this->attestation;
    }

    /**
     * @param Fichier|null $attestation
     */
    public function setAttestation(?Fichier $attestation): void
    {
        $this->attestation = $attestation;
    }

    /**
     * @return DateTime
     */
    public function getDebut(): ?DateTime
    {
        return $this->debut;
    }

    /**
     * @param DateTime $debut
     */
    public function setDebut(DateTime $debut): void
    {
        $this->debut = $debut;
    }

    /**
     * @return DateTime
     */
    public function getFin(): ?DateTime
    {
        return $this->fin;
    }

    /**
     * @param DateTime $fin
     */
    public function setFin(DateTime $fin): void
    {
        $this->fin = $fin;
    }

    /**
     * @return int
     */
    public function getDuree(): ?int
    {
        return $this->duree;
    }

    /**
     * @param int $duree
     */
    public function setDuree(int $duree): void
    {
        $this->duree = $duree;
    }

    /**
     * @return Doctorant|null
     */
    public function getDoctorant(): ?Doctorant
    {
        return $this->doctorant;
    }

    /**
     * @param Doctorant|null $doctorant
     */
    public function setDoctorant(?Doctorant $doctorant): void
    {
        $this->doctorant = $doctorant;
    }

    /**
     * Retourne les éventuelles validations du type spécifié.
     */
    public function getValidations(string|TypeValidation|null $type = null, bool $historisee = false): Collection
    {
        if ($type instanceof TypeValidation) {
            $type = $type->getCode();
        }

        $validations = $this->validations;
        $validations = $validations->filter(function(ValidationFormationExterne $v) use ($type) {
            return $v->getValidation()->getTypeValidation()->getCode() === $type;
        });
        $validations = $validations->filter(function (ValidationFormationExterne $v) use ($historisee) {
            return !$historisee === $v->estNonHistorise();
        });

        return $validations;
    }

    /**
     * Retourne l'éventuelle validation du type spécifié.
     */
    public function getValidation(string|TypeValidation $type, bool $historisee = false): ?ValidationFormationExterne
    {
        return $this->getValidations($type, $historisee)->first() ?: null;
    }

    public function addValidation(ValidationFormationExterne $validation): static
    {
        $this->validations->add($validation);

        return $this;
    }

    public function removeValidation(ValidationFormationExterne $validation): static
    {
        $this->validations->removeElement($validation);

        return $this;
    }

    public function getResourceId(): string
    {
        return 'FormationExterne';
    }
}