<?php

namespace Application\Entity\Db;

use Application\Entity\AnneeUniv;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use These\Entity\Db\These;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

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
     * @var Collection|\Application\Entity\Db\RapportAvis[]
     */
    private $rapportAvis;

    /**
     * Rapport constructor.
     * @param TypeRapport|null $typeRapport
     */
    public function __construct(TypeRapport $typeRapport = null)
    {
        $this->typeRapport = $typeRapport;
        $this->anneeUniv = (int) (new DateTime('today'))->format('Y');
        $this->rapportValidations = new ArrayCollection();
        $this->rapportAvis = new ArrayCollection();
    }

    /**
     * Représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->fichier->getNom();
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
     * @return AnneeUniv
     */
    public function getAnneeUniv(): AnneeUniv
    {
        return AnneeUniv::fromPremiereAnnee($this->anneeUniv);
    }

    /**
     * @param int|AnneeUniv $anneeUniv
     * @return self
     */
    public function setAnneeUniv($anneeUniv): self
    {
        if ($anneeUniv instanceof AnneeUniv) {
            $anneeUniv = $anneeUniv->getPremiereAnnee();
        }

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
        return $this->estFinal ? 'Fin de thèse' : 'Annuel';
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
     * Retourne le code du type de validation attendue.
     *
     * @return string
     */
    public function getCodeTypeValidationAttendu(): string
    {
        switch ($code = $this->getTypeRapport()->getCode()) {
            case TypeRapport::RAPPORT_ACTIVITE:
                return TypeValidation::CODE_RAPPORT_ACTIVITE;
            case TypeRapport::RAPPORT_CSI:
                return TypeValidation::CODE_RAPPORT_CSI;
            case TypeRapport::RAPPORT_MIPARCOURS:
                return TypeValidation::CODE_RAPPORT_MIPARCOURS;
            default:
                throw new \InvalidArgumentException("Code non prévu : " . $code);
        }
    }

    /**
     * Retourne l'éventuelle validation du type spécifié.
     *
     * @return RapportValidation|null
     */
    public function getRapportValidation(): ?RapportValidation
    {
        $type = $this->getCodeTypeValidationAttendu();

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
     * @return \Application\Entity\Db\RapportAvis|null
     */
    public function getRapportAvis(): ?RapportAvis
    {
        $rapportsAvis = $this->rapportAvis->filter(function(RapportAvis $rapportAvis) {
            return $rapportAvis->estNonHistorise();
        });

        return $rapportsAvis->first() ?: null;
    }

    /**
     * @param \Application\Entity\Db\RapportAvis $rapportAvis
     * @return self
     */
    public function addRapportAvis(RapportAvis $rapportAvis): self
    {
        $this->rapportAvis->add($rapportAvis);

        return $this;
    }

    /**
     * @param \Application\Entity\Db\RapportAvis $rapportAvis
     * @return self
     */
    public function removeRapportAvis(RapportAvis $rapportAvis): self
    {
        $this->rapportAvis->removeElement($rapportAvis);

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
            $these->getEtablissement()->getStructure()->getCode(),
            ($ed = $these->getEcoleDoctorale()) ? $ed->getStructure()->getCode() : "ED_inconnue",
            ($ur = $these->getUniteRecherche()) ? $ur->getStructure()->getCode() : "UR_inconnue",
            $this->getFichier()->getNom()
        );
    }
}
