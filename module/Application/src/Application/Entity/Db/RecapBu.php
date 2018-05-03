<?php

namespace Application\Entity\Db;

class RecapBu
{
    /** @var integer $id */
    private $id;
    /** @var These $these */
    protected $these;
    /** @var Diffusion $diffusion */
    protected $diffusion;
    /** @var RdvBu $rdvBu */
    protected $rdvBu;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return RecapBu
     */
    public function setThese($these)
    {
        $this->these = $these;
        return $this;
    }


    /**
     * @return Diffusion
     */
    public function getDiffusion()
    {
        return $this->diffusion;
    }

    /**
     * @param Diffusion $diffusion
     * @return RecapBu
     */
    public function setDiffusion($diffusion)
    {
        $this->diffusion = $diffusion;
        return $this;
    }

    /**
     * @return RdvBu
     */
    public function getRdvBu()
    {
        return $this->rdvBu;
    }

    /**
     * @param RdvBu $rdvBu
     * @return RecapBu
     */
    public function setRdvBu($rdvBu)
    {
        $this->rdvBu = $rdvBu;
        return $this;
    }

    public function getIdOrcid() {
        return $this->getDiffusion()->getIdOrcid();
    }

    public function setIdOrcid($orcid) {
        $this->getDiffusion()->setIdOrcid($orcid);
        return $this;
    }

    public function getNNT() {
        return $this->getDiffusion()->getNNT();
    }

    public function setNNT($nnt) {
        $this->getDiffusion()->setNNT($nnt);
        return $this;
    }

    public function getVigilance () {
        return $this->getRdvBu()->getDivers();
    }

    public function setVigilance($divers) {
        $this->getRdvBu()->setDivers($divers);
        return $this;
    }
}