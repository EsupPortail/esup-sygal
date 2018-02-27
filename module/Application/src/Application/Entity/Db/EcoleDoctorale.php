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
class EcoleDoctorale extends Structure implements HistoriqueAwareInterface, SourceAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;


    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $sourceCode;

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
}