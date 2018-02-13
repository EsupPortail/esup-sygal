<?php

namespace Application\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenApp\Util;
use UnicaenImport\Entity\Db\Interfaces\SourceAwareInterface;
use UnicaenImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * EcoleDoctorale
 */
class EcoleDoctorale implements HistoriqueAwareInterface, SourceAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    /**
     * @var string
     */
    protected $libelle;

    /**
     * @var string
     */
    protected $sigle;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $sourceCode;

    /**
     * @var string
     */
    protected $cheminLogo;

    /**
     * @var Collection
     */
    protected $ecoleDoctoraleIndividus;

    /**
     * EcoleDoctorale constructor.
     */
    public function __construct()
    {
        $this->ecoleDoctoraleIndividus = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }
    
    /**
     * Set libelleCourt
     *
     * @param string $libelle
     * @return self
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelleCourt
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set libelleLong
     *
     * @param string $sigle
     * @return self
     */
    public function setSigle($sigle)
    {
        $this->sigle = $sigle;

        return $this;
    }

    /**
     * Get libelleLong
     *
     * @return string 
     */
    public function getSigle()
    {
        return $this->sigle;
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
     * Set sourceCode
     *
     * @param string $sourceCode
     * @return self
     */
    public function setSourceCode($sourceCode)
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * Get sourceCode
     *
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return Collection
     */
    public function getEcoleDoctoraleIndividus()
    {
        return $this->ecoleDoctoraleIndividus;
    }

    /**
     * @param EcoleDoctoraleIndividu $edi
     * @return self
     */
    public function addEcoleDoctoraleIndividu(EcoleDoctoraleIndividu $edi)
    {
        $this->ecoleDoctoraleIndividus->add($edi);

        return $this;
    }

    /**
     * @param EcoleDoctoraleIndividu $edi
     * @return self
     */
    public function removeEcoleDoctoraleIndividu(EcoleDoctoraleIndividu $edi)
    {
        $this->ecoleDoctoraleIndividus->removeElement($edi);

        return $this;
    }


    public function getLogoContent()
    {
        if ($this->cheminLogo === null) {
            $image = Util::createImageWithText("Aucun logo pour l'ED|[".$this->getSourceCode()." - ".$this->getSigle()."]",200,200);
            return $image;
        }
        return file_get_contents(APPLICATION_DIR . $this->cheminLogo);

    }

    /**
     * @return string
     */
    public function getCheminLogo()
    {
        return $this->cheminLogo;
    }

    /**
     * @param string $cheminLogo
     */
    public function setCheminLogo($cheminLogo)
    {
        $this->cheminLogo = $cheminLogo;
    }


}