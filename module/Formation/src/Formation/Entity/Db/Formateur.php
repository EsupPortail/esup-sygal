<?php

namespace Formation\Entity\Db;

use Individu\Entity\Db\Individu;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Formateur  implements HistoriqueAwareInterface{
    use HistoriqueAwareTrait;

    const ROLE = 'FORMATEUR';

    /** @var int */
    private $id;
    /** @var Individu */
    private $individu;
    /** @var Session */
    private $session;
    /** @var string|null */
    private $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Individu
     */
    public function getIndividu(): Individu
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return Formateur
     */
    public function setIndividu(Individu $individu): Formateur
    {
        $this->individu = $individu;
        return $this;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return Formateur
     */
    public function setSession(Session $session): Formateur
    {
        $this->session = $session;
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
     * @return Formateur
     */
    public function setDescription(?string $description): Formateur
    {
        $this->description = $description;
        return $this;
    }
}