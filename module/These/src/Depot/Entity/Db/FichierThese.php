<?php

namespace Depot\Entity\Db;

/**
 * FichierThese
 */
class FichierThese
{
    /**
     * @var string|null
     */
    private $retraitement;

    /**
     * @var int|null
     */
    private $estConforme;

    /**
     * @var bool
     */
    private $estPartiel;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Fichier\Entity\Db\Fichier
     */
    private $fichier;

    /**
     * @var \These\Entity\Db\These
     */
    private $these;


    /**
     * Set retraitement.
     *
     * @param string|null $retraitement
     *
     * @return FichierThese
     */
    public function setRetraitement($retraitement = null)
    {
        $this->retraitement = $retraitement;

        return $this;
    }

    /**
     * Get retraitement.
     *
     * @return string|null
     */
    public function getRetraitement()
    {
        return $this->retraitement;
    }

    /**
     * Set estConforme.
     *
     * @param int|null $estConforme
     *
     * @return FichierThese
     */
    public function setEstConforme($estConforme = null)
    {
        $this->estConforme = $estConforme;

        return $this;
    }

    /**
     * Get estConforme.
     *
     * @return int|null
     */
    public function getEstConforme()
    {
        return $this->estConforme;
    }

    /**
     * Set estPartiel.
     *
     * @param bool $estPartiel
     *
     * @return FichierThese
     */
    public function setEstPartiel($estPartiel)
    {
        $this->estPartiel = $estPartiel;

        return $this;
    }

    /**
     * Get estPartiel.
     *
     * @return bool
     */
    public function getEstPartiel()
    {
        return $this->estPartiel;
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
     * Set fichier.
     *
     * @param \Fichier\Entity\Db\Fichier|null $fichier
     *
     * @return FichierThese
     */
    public function setFichier(\Fichier\Entity\Db\Fichier $fichier = null)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier.
     *
     * @return \Fichier\Entity\Db\Fichier|null
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * Set these.
     *
     * @param \These\Entity\Db\These|null $these
     *
     * @return FichierThese
     */
    public function setThese(\These\Entity\Db\These $these = null)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Get these.
     *
     * @return \These\Entity\Db\These|null
     */
    public function getThese()
    {
        return $this->these;
    }
}
