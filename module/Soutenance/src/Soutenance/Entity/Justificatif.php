<?php

namespace Soutenance\Entity;

use Depot\Entity\Db\FichierHDR;
use Depot\Entity\Db\FichierThese;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Justificatif implements HistoriqueAwareInterface {
    use HistoriqueAwareTrait;
    private ?int $id = null;
    private ?Proposition $proposition = null;
    private ?FichierThese $fichierThese = null;
    private ?FichierHDR $fichierHDR = null;
    private int $fichier;

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

    public function getFichier() : FichierThese|FichierHDR|null
    {
        return $this->proposition instanceof PropositionThese ? $this->fichierThese : $this->fichierHDR;
    }

    public function setFichier(FichierThese|FichierHDR $fichier) : void
    {
        if($this->proposition instanceof PropositionThese){
            $this->fichierThese = $fichier;
        }else{
            $this->fichierHDR = $fichier;
        }
    }

    public function getMembre() : ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre) : void
    {
        $this->membre = $membre;
    }

    /**
     * Set a resolver for fichier.
     */
    public function setFichierResolver(callable $resolver): void
    {
        $this->fichierResolver = $resolver;
    }
}