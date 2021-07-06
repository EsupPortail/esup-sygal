<?php

namespace Formation\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class EnqueteQuestion implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;
    /** @var string */
    private $libelle;
    /** @var string|null */
    private $description;
    /** @var int|null */
    private $ordre;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     * @return EnqueteQuestion
     */
    public function setLibelle(string $libelle): EnqueteQuestion
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return EnqueteQuestion
     */
    public function setDescription(?string $description): EnqueteQuestion
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    /**
     * @param int|null $ordre
     * @return EnqueteQuestion
     */
    public function setOrdre(?int $ordre): EnqueteQuestion
    {
        $this->ordre = $ordre;
        return $this;
    }

}