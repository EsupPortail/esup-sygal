<?php

namespace Soutenance\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Qualite implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const ID_QUALITE_PAR_DEFAUT = 1;

    const RANG_LIBELLE_AUCUN = "Aucun";

    /** @var int */
    private $id;
    /** @var string */
    private $libelle;
    private ?string $rang = null;
    /** @var string */
    private $hdr;
    /** @var string */
    private $emeritat;
    /** @var string */
    private $justificatif;
    /** @var string */
    private $admission;

    /** @var ArrayCollection (QualiteLibelleSupplementaire) */
    private $libellesSupplementaires;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return Qualite
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getRang(): ?string
    {
        return $this->rang;
    }

    public function isRangB(): bool
    {
        return $this->rang === 'B';
    }

    public function setRang(?string $rang): static
    {
        $this->rang = $rang;
        return $this;
    }

    /**
     * @return string
     */
    public function getHdr()
    {
        return $this->hdr;
    }

    /**
     * @param string $hdr
     * @return Qualite
     */
    public function setHdr($hdr)
    {
        $this->hdr = $hdr;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHDR()
    {
        return ($this->hdr === 'O');
    }

    /**
     * @return string
     */
    public function getEmeritat()
    {
        return $this->emeritat;
    }

    /**
     * @return bool
     */
    public function isEmeritat()
    {
        return ($this->emeritat === 'O');
    }

    /**
     * @param string $emeritat
     * @return Qualite
     */
    public function setEmeritat($emeritat)
    {
        $this->emeritat = $emeritat;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJustificatif(): ?string
    {
        return $this->justificatif;
    }

    /**
     * @param string $justificatif
     * @return Qualite
     */
    public function setJustificatif(string $justificatif): Qualite
    {
        $this->justificatif = $justificatif;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdmission(): ?string
    {
        return $this->admission;
    }

    /**
     * @param string $admission
     * @return Qualite
     */
    public function setAdmission(string $admission): Qualite
    {
        $this->admission = $admission;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmission()
    {
        return ($this->admission === 'O');
    }

    /**
     * @return ArrayCollection
     */
    public function getLibellesSupplementaires()
    {
        return $this->libellesSupplementaires;
    }

    public function __toString()
    {
        return $this->getLibelle();
    }

}

