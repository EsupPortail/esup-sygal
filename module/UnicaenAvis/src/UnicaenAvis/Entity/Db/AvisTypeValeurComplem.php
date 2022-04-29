<?php

namespace UnicaenAvis\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AvisTypeValeurComplem
 */
class AvisTypeValeurComplem
{
    const TYPE_COMPLEMENT_TEXTAREA = 'textarea';
    const TYPE_COMPLEMENT_CHECKBOX = 'checkbox';
    const TYPE_COMPLEMENT_INFORMATION = 'information';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $libelle;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $ordre;

    /**
     * @var bool
     */
    private $obligatoire;

    /**
     * @var bool
     */
    private $obligatoireUnAuMoins;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisTypeValeur
     */
    private AvisTypeValeur $avisTypeValeur;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisTypeValeurComplem|null
     */
    private ?AvisTypeValeurComplem $avisTypeValeurComplemParent = null;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisTypeValeurComplem[]|Collection
     */
    private Collection $avisTypeValeurComplemsEnfants;


    /**
     * Méthode de tri selon l'attribut 'ordre'.
     *
     * @param \UnicaenAvis\Entity\Db\AvisTypeValeurComplem $atc1
     * @param \UnicaenAvis\Entity\Db\AvisTypeValeurComplem $atc2
     * @return int
     */
    static public function sorterByOrdre(AvisTypeValeurComplem $atc1, AvisTypeValeurComplem $atc2): int
    {
        return $atc1->getOrdre() <=> $atc2->getOrdre();
    }

    public function __construct()
    {
        $this->avisTypeValeurComplemsEnfants = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return AvisTypeValeurComplem
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return AvisTypeValeurComplem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     *
     * @return AvisTypeValeurComplem
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set obligatoire.
     *
     * @param bool $obligatoire
     *
     * @return AvisTypeValeurComplem
     */
    public function setObligatoire($obligatoire)
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    /**
     * Get obligatoire.
     *
     * @return bool
     */
    public function isObligatoire()
    {
        return $this->obligatoire;
    }

    /**
     * @return bool
     */
    public function isObligatoireUnAuMoins(): bool
    {
        return $this->obligatoireUnAuMoins;
    }

    /**
     * @param bool $obligatoireUnAuMoins
     * @return self
     */
    public function setObligatoireUnAuMoins(bool $obligatoireUnAuMoins): self
    {
        $this->obligatoireUnAuMoins = $obligatoireUnAuMoins;
        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set avisTypeValeur.
     *
     * @param \UnicaenAvis\Entity\Db\AvisTypeValeur|null $avisTypeValeur
     *
     * @return AvisTypeValeurComplem
     */
    public function setAvisTypeValeur(AvisTypeValeur $avisTypeValeur = null): AvisTypeValeurComplem
    {
        $this->avisTypeValeur = $avisTypeValeur;

        return $this;
    }

    /**
     * Get avisTypeValeur.
     *
     * @return \UnicaenAvis\Entity\Db\AvisTypeValeur|null
     */
    public function getAvisTypeValeur(): ?AvisTypeValeur
    {
        return $this->avisTypeValeur;
    }

    /**
     * @return \UnicaenAvis\Entity\Db\AvisTypeValeurComplem|null
     */
    public function getAvisTypeValeurComplemParent(): ?AvisTypeValeurComplem
    {
        return $this->avisTypeValeurComplemParent;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|\UnicaenAvis\Entity\Db\AvisTypeValeurComplem[]
     */
    public function getAvisTypeValeurComplemsEnfants(): Collection
    {
        return $this->avisTypeValeurComplemsEnfants;
    }
}
