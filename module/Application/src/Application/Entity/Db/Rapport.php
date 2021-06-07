<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class Rapport implements ResourceInterface, HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const RESOURCE_ID = 'Rapport';

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $anneeUniv;

    /**
     * @var bool
     */
    private $estFinal = false;

    /**
     * @var TypeRapport
     */
    private $typeRapport;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * @var These
     */
    private $these;

    /**
     * @var Collection|RapportValidation[]
     */
    private $rapportValidations;

    /**
     * Rapport constructor.
     * @param TypeRapport|null $typeRapport
     */
    public function __construct(TypeRapport $typeRapport = null)
    {
        $this->typeRapport = $typeRapport;
        $this->rapportValidations = new ArrayCollection();
    }

    /**
     * Représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fichier;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAnneeUniv()
    {
        return $this->anneeUniv;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getAnneeUnivToString($separator = '/')
    {
        return $this->anneeUniv . $separator . ($this->anneeUniv + 1);
    }

    /**
     * @param int $anneeUniv
     * @return self
     */
    public function setAnneeUniv($anneeUniv)
    {
        $this->anneeUniv = $anneeUniv;
        return $this;
    }

    /**
     * @return bool
     */
    public function estFinal(): bool
    {
        return $this->estFinal;
    }

    /**
     * @return string
     */
    public function getEstFinalToString(): string
    {
        return $this->estFinal ? 'Fin' : 'Annuel';
    }

    /**
     * @param bool $estFinal
     * @return self
     */
    public function setEstFinal($estFinal = true): self
    {
        $this->estFinal = $estFinal;

        return $this;
    }

    /**
     * @return TypeRapport|null
     */
    public function getTypeRapport(): ?TypeRapport
    {
        return $this->typeRapport;
    }

    /**
     * @return string
     */
    public function getTypeRapportToString(): string
    {
        return $this->typeRapport->estRapportActivite() ?
            $this->typeRapport . " ({$this->getEstFinalToString()})" :
            (string) $this->typeRapport;
    }

    /**
     * @param TypeRapport $typeRapport
     * @return Rapport
     */
    public function setTypeRapport(TypeRapport $typeRapport)
    {
        $this->typeRapport = $typeRapport;
        return $this;
    }

    /**
     * @return Fichier
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * @param Fichier $fichier
     * @return self
     */
    public function setFichier(Fichier $fichier)
    {
        $this->fichier = $fichier;

        return $this;
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
     * Retourne l'éventuelle validation du type spécifié.
     *
     * @param TypeValidation|string $type
     * @return RapportValidation|null
     */
    public function getRapportValidationOfType($type): ?RapportValidation
    {
        if ($type instanceof TypeValidation) {
            $type = $type->getCode();
        }

        $validations = $this->rapportValidations;
        $validations = $validations->filter(function(RapportValidation $v) use ($type) {
            return $v->getTypeValidation()->getCode() === $type;
        });
        $validations = $validations->filter(function(RapportValidation $v) {
            return $v->estNonHistorise();
        });

        return $validations->first() ?: null;
    }

    /**
     * @return Collection
     */
    public function getRapportValidations()
    {
        return $this->rapportValidations;
    }

    /**
     * @param RapportValidation $validation
     * @return self
     */
    public function addRapportValidation(RapportValidation $validation): self
    {
        $this->rapportValidations->add($validation);

        return $this;
    }

    /**
     * @param RapportValidation $validation
     * @return self
     */
    public function removeRapportValidation(RapportValidation $validation): self
    {
        $this->rapportValidations->removeElement($validation);

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }

    /**
     * @return string
     */
    public function generateInternalPathForZipArchive(): string
    {
        $these = $this->getThese();

        return sprintf('%s/%s/%s/%s',
            $these->getEtablissement()->getCode(),
            ($ed = $these->getEcoleDoctorale()) ? $ed->getStructure()->getCode() : "ED_inconnue",
            ($ur = $these->getUniteRecherche()) ? $ur->getStructure()->getCode() : "UR_inconnue",
            $this->getFichier()->getNom()
        );
    }
}
