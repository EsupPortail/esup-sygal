<?php

namespace Formation\Entity\Db;

use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Seance implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    /** @var int */
    private $id;

    /** @var Session|null */
    private $session;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @param Session|null $session
     * @return Seance
     */
    public function setSession(?Session $session): Seance
    {
        $this->session = $session;
        return $this;
    }

}