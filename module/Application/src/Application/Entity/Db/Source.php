<?php

namespace Application\Entity\Db;

/**
 * Source
 */
class Source extends \UnicaenDbImport\Entity\Db\Source
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
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set importable
     *
     * @param boolean $importable
     */
    public function setImportable(bool $importable)
    {
        $this->importable = $importable;
    }

    /**
     * Get importable
     *
     * @return boolean
     */
    public function getImportable(): bool
    {
        return $this->importable;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     */
    public function setLibelle(string $libelle)
    {
        $this->libelle = $libelle;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Etablissement|null
     */
    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    /**
     * Retourne la représentation littérale de cet objet.
     *
     * @return string
     */
    public function __toString(): string
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
    function __debugInfo(): array
    {
        return [
            'libelle' => $this->libelle,
        ];
    }
}