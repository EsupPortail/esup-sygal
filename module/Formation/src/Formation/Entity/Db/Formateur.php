<?php

namespace Formation\Entity\Db;

use Individu\Entity\Db\Individu;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Formateur  implements HistoriqueAwareInterface{
    use HistoriqueAwareTrait;

    const ROLE = 'FORMATEUR';

    private int $id;
    private ?Individu $individu = null;
    private ?Session $session = null;
    private ?string $description = null;

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