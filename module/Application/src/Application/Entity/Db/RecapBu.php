<?php

namespace Application\Entity\Db;

class RecapBu {

    private $id;
    protected $these;
    protected $orcid;
    protected $nnt;
    protected $vigilance;

    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param mixed $these
     */
    public function setThese($these)
    {
        $this->these = $these;
    }


    /**
     * @return mixed
     */
    public function getOrcid()
    {
        return $this->orcid;
    }

    /**
     * @param mixed $orcid
     */
    public function setOrcid($orcid)
    {
        $this->orcid = $orcid;
    }

    /**
     * @return mixed
     */
    public function getNNT()
    {
        return $this->nnt;
    }

    /**
     * @param mixed $nnt
     */
    public function setNNT($nnt)
    {
        $this->nnt = $nnt;
    }

    /**
     * @return mixed
     */
    public function getVigilance()
    {
        return $this->vigilance;
    }

    /**
     * @param mixed $vigilance
     */
    public function setVigilance($vigilance)
    {
        $this->vigilance = $vigilance;
    }

//    public function getArrayCopy() {
//        $data = array();
//        $data['id'] = $this->id;
//        $data['orcid'] = $this->orcid;
//        $data['nnt'] = $this->nnt;
//        $data['vigilance'] = $this->vigilance;
//        return $data;
//    }
//
//    public function exchangeArray($recap) {
//        $data = array();
//        $data['id'] = $recap->id;
//        $data['orcid'] = $recap->orcid;
//        $data['nnt'] = $recap->nnt;
//        $data['vigilance'] = $recap->vigilance;
//        return $data;
//    }
}