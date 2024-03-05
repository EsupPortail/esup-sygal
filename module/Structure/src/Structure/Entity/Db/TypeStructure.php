<?php

namespace Structure\Entity\Db;

/**
 * TypeStructure
 */
class TypeStructure
{
    const CODE_ETABLISSEMENT   = 'etablissement';
    const CODE_ECOLE_DOCTORALE = 'ecole-doctorale';
    const CODE_UNITE_RECHERCHE = 'unite-recherche';
    const CODE_COMPOSANTE_ENSEIGNEMENT = 'composante-enseignement';

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $libelle;

    /**
     * @var integer
     */
    private $id;

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * Set code
     *
     * @param string $code
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
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
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
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
    public function isEtablissement()
    {
        return $this->getCode() === self::CODE_ETABLISSEMENT;
    }

    /**
     * @return bool
     */
    public function isEcoleDoctorale()
    {
        return $this->getCode() === self::CODE_ECOLE_DOCTORALE;
    }

    /**
     * @return bool
     */
    public function isUniteRecherche()
    {
        return $this->getCode() === self::CODE_UNITE_RECHERCHE;
    }

    /**
     * @return bool
     */
    public function isComposanteEnseignement()
    {
        return $this->getCode() === self::CODE_COMPOSANTE_ENSEIGNEMENT;
    }
}