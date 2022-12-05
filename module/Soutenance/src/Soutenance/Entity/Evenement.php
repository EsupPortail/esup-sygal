<?php

namespace Soutenance\Entity;

use DateTime;

class Evenement {

    CONST EVENEMENT_SIGNATURE  = 1;
    CONST EVENEMENT_ENGAGEMENT = 2;
    CONST EVENEMENT_PRERAPPORT = 3;

    /** @var int */
    private $id;
    /** @var Proposition */
    private $proposition;
    /** @var int */
    private $type;
    /** @var DateTime */
    private $date;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Proposition
     */
    public function getProposition(): ?Proposition
    {
        return $this->proposition;
    }

    /**
     * @param Proposition $proposition
     * @return Evenement
     */
    public function setProposition(Proposition $proposition): Evenement
    {
        $this->proposition = $proposition;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Evenement
     */
    public function setType(int $type): Evenement
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return Evenement
     */
    public function setDate(DateTime $date): Evenement
    {
        $this->date = $date;
        return $this;
    }

}