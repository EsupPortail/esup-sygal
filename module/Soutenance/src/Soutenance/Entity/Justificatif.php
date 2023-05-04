<?php

namespace Soutenance\Entity;

use Depot\Entity\Db\FichierThese;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Justificatif implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;

    private ?int $id = null;
    private ?Proposition $proposition = null;
    private ?FichierThese $fichier = null;
    private ?Membre $membre = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getProposition() : ?Proposition
    {
        return $this->proposition;
    }

    public function setProposition(?Proposition $proposition) : void
    {
        $this->proposition = $proposition;
    }

    public function getFichier() : ?FichierThese
    {
        return $this->fichier;
    }

    public function setFichier(FichierThese $fichier) : void
    {
        $this->fichier = $fichier;
    }

    public function getMembre() : ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre) : void
    {
        $this->membre = $membre;
    }

}