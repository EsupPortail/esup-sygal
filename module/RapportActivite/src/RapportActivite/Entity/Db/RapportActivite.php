<?php

namespace RapportActivite\Entity\Db;

use Application\Entity\AnneeUniv;
use Application\Entity\Db\Fichier;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\TypeValidation;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenAvis\Entity\Db\AvisType;

class RapportActivite implements ResourceInterface, HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const RESOURCE_ID = 'RapportActivite';

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
     * @var Collection|RapportActiviteValidation[]
     */
    private $rapportValidations;

    /**
     * @var Collection|\RapportActivite\Entity\Db\RapportActiviteAvis[]
     */
    private $rapportAvis;

    /**
     * @var \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    private ?RapportActiviteAvis $rapportAvisPossible = null;

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
        return $this->estFinal ? 'Fin de contrat' : 'Annuel';
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
        return $this->typeRapport . ($this->estFinal ? ' de fin de contrat' : ' annuel');
    }

    /**
     * @param TypeRapport $typeRapport
     * @return RapportActivite
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
    public function setThese(These $these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Détermine si ce rapport a été validé.
     *
     * @return bool
     */
    public function estValide(): bool
    {
        return $this->getRapportValidation() !== null;
    }

    /**
     * Retourne l'éventuelle validation de ce rapport.
     *
     * @return RapportActiviteValidation|null
     * @deprecated À supprimer pour utiliser {@see \RapportActivite\Service\Validation\RapportActiviteValidationService::findByRapportActivite()}
     */
    public function getRapportValidation(): ?RapportActiviteValidation
    {
        $validations = $this->rapportValidations;
        $validations = $validations->filter(function(RapportActiviteValidation $v) {
            return $v->getTypeValidation()->getCode() === TypeValidation::CODE_RAPPORT_ACTIVITE;
        });
        $validations = $validations->filter(function(RapportActiviteValidation $v) {
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
     * @param RapportActiviteValidation $validation
     * @return self
     */
    public function addRapportValidation(RapportActiviteValidation $validation): self
    {
        $this->rapportValidations->add($validation);

        return $this;
    }

    /**
     * @param RapportActiviteValidation $validation
     * @return self
     */
    public function removeRapportValidation(RapportActiviteValidation $validation): self
    {
        $this->rapportValidations->removeElement($validation);

        return $this;
    }

    /**
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     * @deprecated
     */
    public function getFirstRapportAvis(): ?RapportActiviteAvis
    {
        $rapportsAvis = $this->rapportAvis->filter(function(RapportActiviteAvis $rapportAvis) {
            return $rapportAvis->estNonHistorise();
        });

        return $rapportsAvis->first() ?: null;
    }

    /**
     * Injecte une instance de {@see \RapportActivite\Entity\Db\RapportActiviteAvis} qu'il est possible de créer
     * pour ce rapport.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis|null $rapportAvis
     * @return \RapportActivite\Entity\Db\RapportActivite
     */
    public function setRapportAvisPossible(?RapportActiviteAvis $rapportAvis): RapportActivite
    {
        $this->rapportAvisPossible = $rapportAvis;
        if ($this->rapportAvisPossible) {
            $this->rapportAvisPossible->setRapportActivite($this);
        }

        return $this;
    }

    /**
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis|null
     */
    public function getRapportAvisPossible(): ?RapportActiviteAvis
    {
        return $this->rapportAvisPossible;
    }

    /**
     * @return \RapportActivite\Entity\Db\RapportActiviteAvis[]|Collection
     */
    public function getRapportAvis(): Collection
    {
        return $this->rapportAvis->filter(function(RapportActiviteAvis $rapportAvis) {
            return $rapportAvis->estNonHistorise();
        });
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     * @return self
     */
    public function addRapportAvis(RapportActiviteAvis $rapportAvis): self
    {
        $this->rapportAvis->add($rapportAvis);

        return $this;
    }

    /**
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportAvis
     * @return self
     */
    public function removeRapportAvis(RapportActiviteAvis $rapportAvis): self
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
            $these->getEtablissement()->getCode(),
            ($ed = $these->getEcoleDoctorale()) ? $ed->getStructure()->getCode() : "ED_inconnue",
            ($ur = $these->getUniteRecherche()) ? $ur->getStructure()->getCode() : "UR_inconnue",
            $this->getFichier()->getNom()
        );
    }

    /**
     * Teste si le fichier du rapport supporte l'ajout de la page de validation.
     *
     * @return bool
     */
    public function supporteAjoutPageValidation(): bool
    {
        return $this->getFichier()->isTypeMimePdf();
    }
}
