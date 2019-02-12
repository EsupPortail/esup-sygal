<?php

namespace Application\Entity\Db;

use UnicaenImport\Entity\Db\Interfaces\SourceInterface;

/**
 * Source
 */
class Source implements SourceInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var boolean
     */
    protected $importable;

    /**
     * @var string
     */
    protected $libelle;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Source
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
     * Set importable
     *
     * @param boolean $importable
     *
     * @return Source
     */
    public function setImportable($importable)
    {
        $this->importable = $importable;

        return $this;
    }

    /**
     * Get importable
     *
     * @return boolean
     */
    public function getImportable()
    {
        return $this->importable;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Source
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
     * @return Etablissement
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLibelle();
    }

    /**
     * @since PHP 5.6.0
     * This method is called by var_dump() when dumping an object to get the properties that should be shown.
     * If the method isn't defined on an object, then all public, protected and private properties will be shown.
     *
     * @return array
     * @link  http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     */
    function __debugInfo()
    {
        return [
            'libelle' => $this->libelle,
        ];
    }
}