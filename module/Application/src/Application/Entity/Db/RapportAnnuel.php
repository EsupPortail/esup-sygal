<?php

namespace Application\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class RapportAnnuel implements ResourceInterface, HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const RESOURCE_ID = 'RapportAnnuel';

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $anneeUniv;

    /**
     * @var bool
     */
    private $estFinal = false;

    /**
     * @var Fichier
     */
    private $fichier;

    /**
     * @var These
     */
    private $these;

    /**
     * Représentation littérale de cet objet.
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fichier;
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
     * @return int
     */
    public function getAnneeUniv()
    {
        return $this->anneeUniv;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getAnneeUnivToString($separator = '/')
    {
        return $this->anneeUniv . $separator . ($this->anneeUniv + 1);
    }

    /**
     * @param int $anneeUniv
     * @return self
     */
    public function setAnneeUniv($anneeUniv)
    {
        $this->anneeUniv = $anneeUniv;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEstFinal()
    {
        return $this->estFinal;
    }

    /**
     * @param bool $estFinal
     * @return self
     */
    public function setEstFinal($estFinal = true)
    {
        $this->estFinal = $estFinal;

        return $this;
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

    /**
     * @return These
     */
    public function getThese()
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return self
     */
    public function setThese($these)
    {
        $this->these = $these;

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return self::RESOURCE_ID;
    }

    /**
     * @return string
     */
    public function generateInternalPathForZipArchive()
    {
        return sprintf('%s/%s/%s/%s',
            $this->getThese()->getEtablissement()->getStructure()->getCode(),
            ($ed = $this->getThese()->getEcoleDoctorale()) ? $ed->getStructure()->getCode() : "ED_inconnue",
            ($ur = $this->getThese()->getUniteRecherche()) ? $ur->getStructure()->getCode() : "UR_inconnue",
            $this->getFichier()->getNom()
        );
    }
}
