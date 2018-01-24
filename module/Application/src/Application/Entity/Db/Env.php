<?php

namespace Application\Entity\Db;

/**
 * Env
 */
class Env
{
    /**
     * @var integer
     */
    private $annee;

    /**
     * @var string
     */
    private $libEtab;

    /**
     * @var string
     */
    private $libEtabA;

    /**
     * @var string
     */
    private $libEtabLe;

    /**
     * @var string
     */
    private $libEtabDe;

    /**
     * @var string
     */
    private $libPresidLe;

    /**
     * @var string
     */
    private $libPresidDe;

    /**
     * @var string
     */
    private $nomPresid;

    /**
     * @var string
     */
    private $libComue;

    /**
     * @var string
     */
    private $emailAssistance;

    /**
     * @var string
     */
    private $emailBU;

    /**
     * @var string
     */
    private $emailBdD;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set annee
     *
     * @param integer $annee
     *
     * @return Env
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return integer
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set libEtab
     *
     * @param string $libEtab
     *
     * @return Env
     */
    public function setLibEtab($libEtab)
    {
        $this->libEtab = $libEtab;

        return $this;
    }

    /**
     * Get libEtab
     *
     * @return string
     */
    public function getLibEtab()
    {
        return $this->libEtab;
    }

    /**
     * Set libEtabA
     *
     * @param string $libEtabA
     *
     * @return Env
     */
    public function setLibEtabA($libEtabA)
    {
        $this->libEtabA = $libEtabA;

        return $this;
    }

    /**
     * Get libEtabA
     *
     * @return string
     */
    public function getLibEtabA()
    {
        return $this->libEtabA;
    }

    /**
     * Set libEtabLe
     *
     * @param string $libEtabLe
     *
     * @return Env
     */
    public function setLibEtabLe($libEtabLe)
    {
        $this->libEtabLe = $libEtabLe;

        return $this;
    }

    /**
     * Get libEtabLe
     *
     * @return string
     */
    public function getLibEtabLe()
    {
        return $this->libEtabLe;
    }

    /**
     * Set libEtabDe
     *
     * @param string $libEtabDe
     *
     * @return Env
     */
    public function setLibEtabDe($libEtabDe)
    {
        $this->libEtabDe = $libEtabDe;

        return $this;
    }

    /**
     * Get libEtabDe
     *
     * @return string
     */
    public function getLibEtabDe()
    {
        return $this->libEtabDe;
    }

    /**
     * Set libPresidLe
     *
     * @param string $libPresidLe
     *
     * @return Env
     */
    public function setLibPresidLe($libPresidLe)
    {
        $this->libPresidLe = $libPresidLe;

        return $this;
    }

    /**
     * Get libPresidLe
     *
     * @return string
     */
    public function getLibPresidLe()
    {
        return $this->libPresidLe;
    }

    /**
     * Set libPresidDe
     *
     * @param string $libPresidDe
     *
     * @return Env
     */
    public function setLibPresidDe($libPresidDe)
    {
        $this->libPresidDe = $libPresidDe;

        return $this;
    }

    /**
     * Get libPresidDe
     *
     * @return string
     */
    public function getLibPresidDe()
    {
        return $this->libPresidDe;
    }

    /**
     * Set nomPresid
     *
     * @param string $nomPresid
     *
     * @return Env
     */
    public function setNomPresid($nomPresid)
    {
        $this->nomPresid = $nomPresid;

        return $this;
    }

    /**
     * Get nomPresid
     *
     * @return string
     */
    public function getNomPresid()
    {
        return $this->nomPresid;
    }

    /**
     * @return string
     */
    public function getLibComue()
    {
        return $this->libComue;
    }

    /**
     * @param string $libComue
     * @return Env
     */
    public function setLibComue($libComue)
    {
        $this->libComue = $libComue;

        return $this;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Env
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return string
     */
    public function getEmailAssistance()
    {
        return $this->emailAssistance;
    }

    /**
     * @param string $emailAssistance
     * @return Env
     */
    public function setEmailAssistance($emailAssistance)
    {
        $this->emailAssistance = $emailAssistance;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailBU()
    {
        return $this->emailBU;
    }

    /**
     * @param string $emailBU
     * @return Env
     */
    public function setEmailBU($emailBU)
    {
        $this->emailBU = $emailBU;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailBdD()
    {
        return $this->emailBdD;
    }

    /**
     * @param string $emailBdD
     * @return $this
     */
    public function setEmailBdD($emailBdD)
    {
        $this->emailBdD = $emailBdD;

        return $this;
    }
}