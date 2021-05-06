<?php

namespace Application\Entity\Db;

/**
 * TypeRapport
 */
class TypeRapport
{
    const RAPPORT_ACTIVITE_ANNUEL = 'RAPPORT_ACTIVITE_ANNUEL';
    const RAPPORT_ACTIVITE_FINTHESE = 'RAPPORT_ACTIVITE_FINTHESE';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $libelleCourt;

    /**
     * @var string
     */
    private $libelleLong;

    /**
     * @var integer
     */
    private $id;

    /**
     * @return bool
     */
    public function estRapportActivite()
    {
        return
            $this->getCode() === self::RAPPORT_ACTIVITE_ANNUEL ||
            $this->getCode() === self::RAPPORT_ACTIVITE_FINTHESE;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelleCourt
     *
     * @param string $libelleCourt
     *
     * @return self
     */
    public function setLibelleCourt($libelleCourt)
    {
        $this->libelleCourt = $libelleCourt;

        return $this;
    }

    /**
     * Get libelleCourt
     *
     * @return string
     */
    public function getLibelleCourt()
    {
        return $this->libelleCourt;
    }

    /**
     * Set libelleLong
     *
     * @param string $libelleLong
     *
     * @return self
     */
    public function setLibelleLong($libelleLong)
    {
        $this->libelleLong = $libelleLong;

        return $this;
    }

    /**
     * Get libelleLong
     *
     * @return string
     */
    public function getLibelleLong()
    {
        return $this->libelleLong;
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
}