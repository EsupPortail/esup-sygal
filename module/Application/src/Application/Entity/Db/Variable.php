<?php

namespace Application\Entity\Db;

use Structure\Entity\Db\Etablissement;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/**
 * Variable
 */
class Variable implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    const CODE_EMAIL_ASSISTANCE              = 'EMAIL_ASSISTANCE';
    const CODE_ETB_ART_ETB_LIB               = 'ETB_ART_ETB_LIB';   // Ex: "L'"
    const CODE_ETB_LIB                       = 'ETB_LIB';           // Ex: "Université de Caen Normandie"
    const CODE_ETB_LIB_TIT_RESP              = 'ETB_LIB_TIT_RESP';  // Ex: "Le Président"
    const CODE_ETB_LIB_NOM_RESP              = 'ETB_LIB_NOM_RESP';  // Ex: "Pierre Denise"

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $valeur;

    /**
     * @var \DateTime
     */
    private $dateDebutValidite;

    /**
     * @var \DateTime
     */
    private $dateFinValidite;

    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var integer
     */
    private $id;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Variable
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Variable
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     *
     * @return Variable
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * @return \DateTime
     */
    public function getDateDebutValidite()
    {
        return $this->dateDebutValidite;
    }

    /**
     * @param \DateTime $dateDebutValidite
     * @return Variable
     */
    public function setDateDebutValidite(\DateTime $dateDebutValidite)
    {
        $this->dateDebutValidite = $dateDebutValidite;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFinValidite()
    {
        return $this->dateFinValidite;
    }

    /**
     * @param \DateTime $dateFinValidite
     * @return Variable
     */
    public function setDateFinValidite(\DateTime $dateFinValidite)
    {
        $this->dateFinValidite = $dateFinValidite;

        return $this;
    }

    /**
     * Set sourceCode
     *
     * @param string $sourceCode
     *
     * @return Variable
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
     * Set id
     *
     * @param integer $id
     *
     * @return Variable
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
     * Retourne l'établissement lié.
     */
    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     *
     * @return Variable
     */
    public function setEtablissement(Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }
}

