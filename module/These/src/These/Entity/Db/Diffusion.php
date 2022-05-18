<?php

namespace These\Entity\Db;

use Application\Constants;
use These\Rule\AutorisationDiffusionRule;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

/**
 * Diffusion
 */
class Diffusion implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const CONFIDENTIELLE_OUI = '1';
    const CONFIDENTIELLE_NON = '0';

    const AUTORISATION_OUI_IMMEDIAT = 2;
    const AUTORISATION_OUI_EMBARGO = 1;
    const AUTORISATION_NON = 0;

    const EMBARGO_DUREE_6_MOIS = '6 mois';
    const EMBARGO_DUREE_1_AN = '1 an';
    const EMBARGO_DUREE_2_ANS = '2 ans';
    const EMBARGO_DUREE_5_ANS = '5 ans';

    const DROIT_AUTEUR_OK_OUI = '1';
    const DROIT_AUTEUR_OK_NON = '0';

    /**
     * @var bool
     */
    private $versionCorrigee = false;

    /**
     * @var boolean
     */
    private $confidentielle;

    /**
     * @var \DateTime
     */
    private $dateFinConfidentialite;

    /**
     * @var boolean
     */
    private $droitAuteurOk;

    /**
     * @var boolean
     */
    private $certifCharteDiff;

    /**
     * @var integer
     */
    private $autorisMel;

    /**
     * @var string
     */
    private $autorisEmbargoDuree;

    /**
     * @var string
     */
    private $autorisMotif;

    /**
     * @var string
     */
    private $orcid;

    /**
     * @var string
     */
    private $halId;

    /**
     * @var string|null
     */
    private ?string $nnt = null;

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
     * @return Diffusion
     */
    public function setVersionCorrigee(bool $versionCorrigee): Diffusion
    {
        $this->versionCorrigee = $versionCorrigee;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getConfidentielle()
    {
        return (bool) $this->confidentielle;
    }

    /**
     * @param boolean $confidentielle
     * @return self
     */
    public function setConfidentielle($confidentielle)
    {
        $this->confidentielle = (bool) $confidentielle;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFinConfidentialite()
    {
        return $this->dateFinConfidentialite;
    }

    /**
     * @return string
     */
    public function getDateFinConfidentialiteToString()
    {
        return $this->dateFinConfidentialite ? $this->dateFinConfidentialite->format(Constants::DATE_FORMAT) : "";
    }

    /**
     * @param \DateTime $dateFinConfidentialite
     * @return self
     */
    public function setDateFinConfidentialite($dateFinConfidentialite)
    {
        $this->dateFinConfidentialite = $dateFinConfidentialite;

        return $this;
    }

    /**
     * Set droitsOk
     *
     * @param boolean $droitAuteurOk
     *
     * @return Diffusion
     */
    public function setDroitAuteurOk($droitAuteurOk = true)
    {
        $this->droitAuteurOk = (bool) $droitAuteurOk;

        return $this;
    }

    /**
     * Get droitsOk
     *
     * @return boolean
     */
    public function getDroitAuteurOk()
    {
        return $this->droitAuteurOk;
    }

    /**
     * @return boolean
     */
    public function getCertifCharteDiff()
    {
        return $this->certifCharteDiff;
    }

    /**
     * @param boolean $certifCharteDiff
     * @return self
     */
    public function setCertifCharteDiff($certifCharteDiff)
    {
        $this->certifCharteDiff = (bool) $certifCharteDiff;

        return $this;
    }

    /**
     * Set autorisMel
     *
     * @param integer $autorisMel
     *
     * @return Diffusion
     */
    public function setAutorisMel($autorisMel)
    {
        $this->autorisMel = (int)$autorisMel;

        return $this;
    }

    /**
     * Get autorisMel
     *
     * @return integer
     */
    public function getAutorisMel()
    {
        return $this->autorisMel;
    }

    /**
     * Set autorisEmbargoDuree
     *
     * @param string $autorisEmbargoDuree
     *
     * @return Diffusion
     */
    public function setAutorisEmbargoDuree($autorisEmbargoDuree)
    {
        $this->autorisEmbargoDuree = $autorisEmbargoDuree;

        return $this;
    }

    /**
     * Get autorisEmbargoDuree
     *
     * @return string
     */
    public function getAutorisEmbargoDuree()
    {
        return $this->autorisEmbargoDuree;
    }

    /**
     * Set autorisMotif
     *
     * @param string $autorisMotif
     *
     * @return Diffusion
     */
    public function setAutorisMotif($autorisMotif)
    {
        $this->autorisMotif = $autorisMotif;

        return $this;
    }

    /**
     * Get autorisMotif
     *
     * @return string
     */
    public function getAutorisMotif()
    {
        return $this->autorisMotif;
    }

    /**
     * @return string
     */
    public function getOrcid()
    {
        return $this->orcid;
    }

    /**
     * @param string $orcid
     * @return self
     */
    public function setOrcid($orcid)
    {
        $this->orcid = $orcid;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHalId()
    {
        return $this->halId;
    }

    /**
     * @param string|null $halId
     * @return Diffusion
     */
    public function setHalId($halId = null): Diffusion
    {
        $this->halId = $halId;

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
     * @return Diffusion
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
     * @return ?string
     */
    public function getNNT(): ?string
    {
        return $this->nnt;
    }

    /**
     * @param string|null $nnt
     * @return Diffusion
     */
    public function setNNT(?string $nnt = null): self
    {
        $this->nnt = $nnt;
        return $this;
    }

    /**
     * Teste si l'exemplaire papier de la thèse est requis, d'après la réponse à l'autorisation de diffusion.
     *
     * @return bool
     */
    public function isRemiseExemplairePapierRequise()
    {
        $rule = new AutorisationDiffusionRule();
        $rule->setDiffusion($this);

        return $rule->computeRemiseExemplairePapierEstRequise();
    }
}

