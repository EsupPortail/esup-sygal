<?php

namespace Admission\Renderer;

use Admission\Entity\Db\Etudiant;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class AdmissionEtudiantTemplateVariable extends AbstractTemplateVariable
{
    private Etudiant $etudiant;

    public function setEtudiant(Etudiant $etudiant): void
    {
        $this->etudiant = $etudiant;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getLibelleCommuneNaissance()
    {
        return $this->etudiant->getLibelleCommuneNaissance();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAdresseCodePostal()
    {
        return $this->etudiant->getAdresseCodePostal();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAdresseNomCommune()
    {
        return $this->etudiant->getAdresseNomCommune();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getCourriel()
    {
        return $this->etudiant->getCourriel();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDenominationEtudiant() : string
    {
        return $this->etudiant->getSexe()." ".$this->etudiant->getNomUsuel()." ".$this->etudiant->getPrenom();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getAdresse() : string
    {
        $adresseParts = [];

        if ($etage = $this->etudiant->getAdresseLigne1Etage()) {
            $adresseParts[] = $etage." (étage)";
        }

        if ($batiment = $this->etudiant->getAdresseLigne2Batiment()) {
            $adresseParts[] = $batiment;
        }

        if ($voie = $this->etudiant->getAdresseLigne3voie()) {
            $adresseParts[] = $voie;
        }

        if ($complement = $this->etudiant->getAdresseLigne4Complement()) {
            $adresseParts[] = $complement;
        }

        // Joindre les parties de l'adresse avec une virgule et un espace
        return implode(', ', $adresseParts);
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getVille() : string|null
    {
        return $this->etudiant->getAdresseNomCommune() ?: $this->etudiant->getAdresseCpVilleEtrangere();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateNaissanceFormat()
    {
        return $this->etudiant->getDateNaissance() ? $this->etudiant->getDateNaissance()->format("d/m/Y") : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getNationaliteLibelle()
    {
        return $this->etudiant->getNationalite() ? $this->etudiant->getNationalite()->getLibelleNationalite() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getPaysNaissanceLibelle()
    {
        return $this->etudiant->getPaysNaissance() ? $this->etudiant->getPaysNaissance()->getLibelle() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getPaysLibelle()
    {
        return $this->etudiant->getAdresseCodePays() ? $this->etudiant->getAdresseCodePays()->getLibelle() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getSituationHandicapLibelle()
    {
        if($this->etudiant->getSituationHandicap()){
            return "Oui";
        }else if($this->etudiant->getSituationHandicap() === false){
            return "Non";
        }else{
            return "<b>Non renseigné</b>";
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getNiveauEtudeInformations(){
        if($this->etudiant->getNiveauEtude() === 1){
            return "Diplôme national tel que le master";
        }else if($this->etudiant->getNiveauEtude() === 2){
            return "Diplôme autre qu'un diplôme national - à titre dérogatoire";
        }else{
            return "<b>Non renseigné</b>";
        }
    }

}