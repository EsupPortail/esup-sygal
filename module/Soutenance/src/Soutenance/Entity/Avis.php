<?php

namespace Soutenance\Entity;

use Depot\Entity\Db\FichierHDR;
use Depot\Entity\Db\FichierThese;
use Fichier\Entity\Db\Fichier;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use Validation\Entity\Db\ValidationHDR;
use Validation\Entity\Db\ValidationThese;

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
    private ?ValidationThese $validationThese = null;
    private ?ValidationHDR $validationHDR = null;
    private ?FichierThese $fichierThese = null;
    private ?FichierHDR $fichierHDR = null;

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

    public function getValidation() : ValidationThese|ValidationHDR|null
    {
        return $this->proposition instanceof PropositionThese ? $this->validationThese : $this->validationHDR;
    }

    public function setValidation(ValidationThese|ValidationHDR $validation) : void
    {
        if($this->proposition instanceof PropositionThese){
            $this->validationThese = $validation;
        }else{
            $this->validationHDR = $validation;
        }
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

//    /**
//     * @deprecated À abandonner car utilise {@see \Soutenance\Entity\Membre::getActeur()}
//     */
//    public function getRapporteur(): ?ActeurThese
//    {
//        if (!isset($this->membre)) return null;
//        return $this->getMembre()->getActeur();
//    }

}