<?php

namespace Application\Entity\Db;

/**
 * ContenuFichier
 */
class ContenuFichier
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $data;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * Set nom
     *
     * @param string $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Fichier
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * @param Fichier $fichier
     * @return self
     */
    public function setFichier(Fichier $fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }
}
