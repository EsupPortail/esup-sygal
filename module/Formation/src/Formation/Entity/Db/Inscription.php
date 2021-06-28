<?php

namespace Formation\Entity\Db;

use Application\Entity\Db\Individu;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Inscription implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    const LISTE_PRINCIPALE = 'P';
    const LISTE_COMPLEMENTAIRE = 'C';

    /** @var int */
    private $id;
    /** @var Session */
    private $session;
    /** @var Individu */
    private $individu;
    /** @var string|null */
    private $liste;
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
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return Inscription
     */
    public function setSession(Session $session): Inscription
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return Individu|null
     */
    public function getIndividu(): ?Individu
    {
        return $this->individu;
    }

    /**
     * @param Individu $individu
     * @return Inscription
     */
    public function setIndividu(Individu $individu): Inscription
    {
        $this->individu = $individu;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getListe(): ?string
    {
        return $this->liste;
    }

    /**
     * @return bool
     */
    public function isListePrincipale() : bool
    {
        return ($this->liste === Inscription::LISTE_PRINCIPALE);
    }

    /**
     * @return bool
     */
    public function isListeComplementaire() : bool
    {
        return ($this->liste === Inscription::LISTE_COMPLEMENTAIRE);
    }

    /**
     * @param string|null $liste
     * @return Inscription
     */
    public function setListe(?string $liste): Inscription
    {
        $this->liste = $liste;
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
     * @return Inscription
     */
    public function setDescription(?string $description): Inscription
    {
        $this->description = $description;
        return $this;
    }

}