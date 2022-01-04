<?php

namespace Soutenance\Entity;

use Application\Entity\Db\These;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Intervention implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const TYPE_DISTANCIEL = 1;
    const TYPE_VISIO_TARDIVE = 2;

    /** @var int */
    private $id;
    /** @var These */
    private $these;
    /** @var int */
    private $type;
    /** @var string */
    private $complement;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return These
     */
    public function getThese(): These
    {
        return $this->these;
    }

    /**
     * @param These $these
     * @return Intervention
     */
    public function setThese(These $these): Intervention
    {
        $this->these = $these;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Intervention
     */
    public function setType(int $type): Intervention
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplement(): ?string
    {
        return $this->complement;
    }

    /**
     * @param string $complement
     * @return Intervention
     */
    public function setComplement(string $complement): Intervention
    {
        $this->complement = $complement;
        return $this;
    }





}