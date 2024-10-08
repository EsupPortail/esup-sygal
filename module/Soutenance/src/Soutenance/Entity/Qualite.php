<?php

namespace Soutenance\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Qualite implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const ID_QUALITE_PAR_DEFAUT = 1;

    /** @var int */
    private $id;
    /** @var string */
    private $libelle;
    /** @var string */
    private $rang;
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

    /**
     * @return string
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * @return bool
     */
    public function isRangA()
    {
        return $this->rang === 'A';
    }

    /**
     * @return bool
     */
    public function isRangB()
    {
        return $this->rang === 'B';
    }

    /**
     * @param string $rang
     * @return Qualite
     */
    public function setRang($rang)
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

