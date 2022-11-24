<?php

namespace Depot\Entity\Db;

use These\Entity\Db\These;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * Diffusion
 */
class Attestation implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    /**
     * @var bool
     */
    private $versionCorrigee = false;

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
     * @var boolean
     */
    private $creationAuto = false;

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
    public function getVersionCorrigee(): bool
    {
        return $this->versionCorrigee;
    }

    /**
     * @param bool $versionCorrigee
     * @return Attestation
     */
    public function setVersionCorrigee(bool $versionCorrigee): Attestation
    {
        $this->versionCorrigee = $versionCorrigee;

        return $this;
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
     * @return bool|null
     */
    public function getExemplaireImprimeConformeAVersionDeposee()
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function estCreationAuto()
    {
        return $this->creationAuto;
    }

    /**
     * @param bool $creationAuto
     * @return Diffusion
     */
    public function setCreationAuto($creationAuto)
    {
        $this->creationAuto = (bool) $creationAuto;

        return $this;
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
