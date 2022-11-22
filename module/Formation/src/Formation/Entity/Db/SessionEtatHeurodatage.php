<?php

namespace Formation\Entity\Db;

use Application\Entity\Db\Utilisateur;
use DateTime;

class SessionEtatHeurodatage
{
    private ?int $id = -1;
    private ?Session $session = null;
    private ?Etat $etat = null;
    private ?DateTime $heurodatage = null;
    private ?Utilisateur $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): void
    {
        $this->session = $session;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): void
    {
        $this->etat = $etat;
    }

    public function getHeurodatage(): ?DateTime
    {
        return $this->heurodatage;
    }

    public function setHeurodatage(?DateTime $heurodatage): void
    {
        $this->heurodatage = $heurodatage;
    }

    /**
     * @return Utilisateur|null
     */
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * @param Utilisateur|null $utilisateur
     */
    public function setUtilisateur(?Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

}