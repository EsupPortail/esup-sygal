<?php

namespace Application\Entity\Db;

/**
 * VWfEtapePertin
 */
class VWfEtapePertin
{
    /**
     * @var These
     */
    private $these;

    /**
     * @var WfEtape
     */
    private $etape;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $ordre;


    /**
     * Set these
     *
     * @param These $these
     *
     * @return VWfEtapePertin
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

    /**
     * Set etape
     *
     * @param WfEtape $etape
     *
     * @return VWfEtapePertin
     */
    public function setEtape(WfEtape $etape = null)
    {
        $this->etape = $etape;

        return $this;
    }

    /**
     * Get etape
     *
     * @return WfEtape
     */
    public function getEtape()
    {
        return $this->etape;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
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

