<?php

namespace Soutenance\Entity;

use Application\Entity\Db\Validation;
use Depot\Entity\Db\FichierThese;
use Fichier\Entity\Db\Fichier;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Avis implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;

    const FAVORABLE = 'Favorable';
    const DEFAVORABLE = 'Défavorable';

    private ?int $id = null;
    private ?Proposition $proposition = null;
    private ?Membre $membre = null;
    private ?string $avis = null;
    private ?string $motif = null;
    private ?Validation $validation = null;
    private ?FichierThese $fichierThese = null;

    /** @var Fichier */
    private $fichier;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposition(): ?Proposition
    {
        return $this->proposition;
    }

    public function setProposition(?Proposition $proposition): void
    {
        $this->proposition = $proposition;
    }

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): void
    {
        $this->membre = $membre;
    }

    public function getAvis(): ?string
    {
        return $this->avis;
    }

    public function setAvis(?string $avis): void
    {
        $this->avis = $avis;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): void
    {
        $this->motif = $motif;
    }

    public function getValidation(): ?Validation
    {
        return $this->validation;
    }

    public function setValidation(?Validation $validation): void
    {
        $this->validation = $validation;
    }

    public function getFichierThese(): ?FichierThese
    {
        return $this->fichierThese;
    }

    public function setFichierThese(?FichierThese $fichierThese): void
    {
        $this->fichierThese = $fichierThese;
    }

    public function setFichier($fichier) : void //todo check that
    {
        $this->fichier = $fichier;
    }


    /** Fonctions de qualité de vie ***********************************************************************************/

    public function getThese(): ?These
    {
        if (!isset($this->proposition)) return null;
        return $this->proposition->getThese();
    }

    public function getRapporteur(): ?Acteur
    {
        if (!isset($this->membre)) return null;
        return $this->getMembre()->getActeur();
    }

    public function getFichier(): ?Fichier
    {
        if (!isset($this->fichierThese)) return null;
        return $this->fichierThese->getFichier();
    }


}