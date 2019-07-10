<?php

namespace Application\Entity\Db;

use Application\Entity\VWorkflowNull;

/**
 * VWorkflow
 */
class VWorkflow
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
     * @var bool
     */
    private $franchie = false;

    /**
     * @var bool
     */
    private $atteignable = false;

    /**
     * @var bool
     */
    private $courante = false;

    /**
     * @var integer
     */
    private $resultat;

    /**
     * @var integer
     */
    private $objectif;

    /**
     * @param These $these
     * @return VWorkflow
     */
    protected function setThese($these)
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
     * @param WfEtape $etape
     * @return VWorkflow
     */
    public function setEtape($etape)
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
     * Get franchie
     *
     * @return bool
     */
    public function getFranchie()
    {
        return $this->franchie;
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
    public function getAtteignable()
    {
        return (bool) $this->atteignable;
    }

    /**
     * @return bool
     */
    public function getCourante()
    {
        return (bool) $this->courante;
    }

    /**
     * Get resultat
     *
     * @return integer
     */
    public function getResultat()
    {
        return $this->resultat;
    }

    /**
     * Get objectif
     *
     * @return integer
     */
    public function getObjectif()
    {
        return $this->objectif;
    }




    /**
     * @return static
     */
    public static function pseudoEtapeFinale()
    {
        $inst = new static();
        $inst->etape = WfEtape::pseudoEtapeFinale();
        $inst->franchie = false;
        $inst->atteignable = true;

        return $inst;
    }

    /**
     * @return bool
     */
    public function estNull()
    {
        return $this instanceof VWorkflowNull;

    }
}
