<?php

namespace Application\Entity\Db;

use UnicaenApp\Util;

/**
 * Structure
 */
class Structure
{
    /**
     * @var string id
     * @var string sigle
     * @var string libelle
     * @var string cheminLogo
     */
    private     $id;
    protected   $sigle;
    protected   $libelle;
    protected   $cheminLogo;

    /**
     * @var TypeStructure
     */
    protected $typeStructure;

    protected $etablissement;
    protected $ecoleDoctorale;
    protected $uniteRecherche;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSigle()
    {
        return $this->sigle;
    }

    /**
     * @param string $sigle
     */
    public function setSigle($sigle)
    {
        $this->sigle = $sigle;
    }

    /**
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
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



    public function __toString()
    {
        return $this->getLibelle();
    }


    public function getLogoContent()
    {
        if ($this->cheminLogo === null) {
            $image = Util::createImageWithText("Aucun logo pour la structure|[".$this->getId()." - ".$this->getSigle()."]",200,200);
            return $image;
        }
        return file_get_contents(APPLICATION_DIR . $this->cheminLogo);
    }

    /**
     * @return TypeStructure
     */
    public function getTypeStructure()
    {
        return $this->typeStructure;
    }

    /**
     * @param TypeStructure $typeStructure
     * @return self
     */
    public function setTypeStructure(TypeStructure $typeStructure)
    {
        $this->typeStructure = $typeStructure;

        return $this;
    }

    /**
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @return EcoleDoctorale
     */
    public function getEcoleDoctorale()
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @return UniteRecherche
     */
    public function getUniteRecherche()
    {
        return $this->uniteRecherche;
    }
}