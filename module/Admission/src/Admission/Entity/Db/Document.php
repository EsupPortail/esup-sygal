<?php
namespace Admission\Entity\Db;

use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Document implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * @var NatureFichier
     */
    private $nature;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return Document
     */
    public function setAdmission(Admission $admission = null)
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission.
     *
     * @return Admission|null
     */
    public function getAdmission()
    {
        return $this->admission;
    }

    /**
     * Set nature.
     *
     * @param NatureFichier|null $nature
     *
     * @return Document
     */
    public function setNature(NatureFichier $nature = null)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get nature.
     *
     * @return NatureFichier|null
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * Set fichier.
     *
     * @param Fichier|null $fichier
     *
     * @return Document
     */
    public function setFichier(Fichier $fichier = null)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier.
     *
     * @return Fichier|null
     */
    public function getFichier()
    {
        return $this->fichier;
    }
}
