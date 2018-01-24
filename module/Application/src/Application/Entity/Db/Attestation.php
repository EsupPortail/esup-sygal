<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * Diffusion
 */
class Attestation implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var boolean
     */
    private $versionDeposeeEstVersionRef;

    /**
     * @var boolean
     */
    private $exemplaireImprimeConformeAVersionDeposee;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var These
     */
    private $these;

    /**
     *
     */
    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return bool
     */
    public function isVersionDeposeeEstVersionRef()
    {
        return $this->versionDeposeeEstVersionRef;
    }

    /**
     * @param bool $versionDeposeeEstVersionRef
     * @return Attestation
     */
    public function setVersionDeposeeEstVersionRef($versionDeposeeEstVersionRef = true)
    {
        $this->versionDeposeeEstVersionRef = $versionDeposeeEstVersionRef;

        return $this;
    }

    /**
     * Get isVersionDeposeeEstVersionRef
     *
     * @return string
     */
    public function isVersionDeposeeEstVersionRefToString()
    {
        if (null === $this->isVersionDeposeeEstVersionRef()) {
            return "";
        }

        return $this->isVersionDeposeeEstVersionRef() ? "Oui" : "Non";
    }

    /**
     * @return bool
     */
    public function isExemplaireImprimeConformeAVersionDeposee()
    {
        return $this->exemplaireImprimeConformeAVersionDeposee;
    }

    /**
     * @param bool $exemplaireImprimeConformeAVersionDeposee
     * @return Attestation
     */
    public function setExemplaireImprimeConformeAVersionDeposee($exemplaireImprimeConformeAVersionDeposee = true)
    {
        $this->exemplaireImprimeConformeAVersionDeposee = $exemplaireImprimeConformeAVersionDeposee;

        return $this;
    }

    /**
     * Get isExemplaireImprimeConformeAVersionDeposee
     *
     * @return string
     */
    public function isExemplaireImprimeConformeAVersionDeposeeToString()
    {
        if (null === $this->isExemplaireImprimeConformeAVersionDeposee()) {
            return "";
        }

        return $this->isExemplaireImprimeConformeAVersionDeposee() ? "Oui" : "Non";
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
     * Set these
     *
     * @param These $these
     *
     * @return Attestation
     */
    public function setThese(These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these
     *
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }
}
