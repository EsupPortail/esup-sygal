<?php

namespace Soutenance\Entity;

use These\Entity\Db\These;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Intervention implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const TYPE_DISTANCIEL = 1;
    const TYPE_VISIO_TARDIVE = 2;

    /** @var int */
    private $id;

    /** @var Proposition */
    private $proposition;

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

    /**
     * @return Proposition
     */
    public function getProposition(): Proposition
    {
        return $this->proposition;
    }

    /**
     * @param Proposition $proposition
     */
    public function setProposition(Proposition $proposition): void
    {
        $this->proposition = $proposition;
    }
}