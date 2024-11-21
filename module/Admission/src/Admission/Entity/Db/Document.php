<?php
namespace Admission\Entity\Db;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fichier\Entity\Db\Fichier;
use Fichier\Entity\Db\NatureFichier;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

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
     * @var Collection
     */
    private $verificationDocument;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->verificationDocument = new ArrayCollection();
    }

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

    /**
     * Get verificationDocument.
     *
     * @return Collection
     */
    public function getVerificationDocument(): Collection
    {
        return $this->verificationDocument;
    }

    /**
     * Add VerificationInscription.
     */
    public function addVerificationDocument(Collection $verificationDocuments)
    {
//        foreach ($verificationDocuments as $vD) {
//            if (!$this->verificationDocument->contains($vD)) {
//                $this->verificationDocument->add($vD);
//            }
//        }

        return $this;
    }

    /**
     * Remove VerificationInscription.
     */
    public function removeVerificationDocument(Collection $verificationDocuments)
    {
        foreach ($verificationDocuments as $vD) {
            $this->verificationDocument->removeElement($vD);
        }
    }
}
