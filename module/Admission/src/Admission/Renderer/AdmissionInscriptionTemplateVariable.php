<?php

namespace Admission\Renderer;

use Admission\Entity\Db\Inscription;
use Application\Renderer\Template\Variable\AbstractTemplateVariable;

class AdmissionInscriptionTemplateVariable extends AbstractTemplateVariable
{
    private Inscription $inscription;

    public function setInscription(Inscription $inscription): void
    {
        $this->inscription = $inscription;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getSpecialiteDoctoratLibelle(): ?string
    {
        return $this->inscription->getSpecialiteDoctorat() ? $this->inscription->getSpecialiteDoctorat()->getLibelle() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getComposanteRattachementLibelle(): ?string
    {
        return $this->inscription->getComposanteDoctorat() ? $this->inscription->getComposanteDoctorat()->getStructure()->getLibelle() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEcoleDoctoraleLibelle(): ?string
    {
        return $this->inscription->getEcoleDoctorale() ? $this->inscription->getEcoleDoctorale()->getStructure()->getLibelle() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getUniteRechercheLibelle(): ?string
    {
        return $this->inscription->getUniteRecherche() ? $this->inscription->getUniteRecherche()->getStructure()->getLibelle() : "<b>Non renseigné</b>";
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEtablissementInscriptionLibelle(): ?string
    {
        return $this->inscription->getEtablissementInscription()?->getStructure()->getLibelle();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDenominationDirecteurThese() : ?string
    {
        return ($this->inscription->getNomDirecteurThese() || $this->inscription->getPrenomDirecteurThese()) ? $this->inscription->getNomDirecteurThese()." ".$this->inscription->getPrenomDirecteurThese() : null;
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getConfidentialiteSouhaiteeLibelle(): string
    {
        if($this->inscription->getConfidentialite() === null){
            return "<b>Non renseigné</b>";
        }else{
            if($this->inscription->getConfidentialite()){
                $dateConfidentialite = $this->inscription->getDateConfidentialite() ? $this->inscription->getDateConfidentialite()->format("d/m/Y") : null;
                return "Oui <br> <ul><li><b>Date de fin de confidentialité souhaitée (limitée à 10 ans) : </b>".$dateConfidentialite."</li></ul>";
            }else{
                return "Non";
            }
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getCotutelleEnvisageeLibelle(): string
    {
        if($this->inscription->getCoTutelle() === null){
            return "<b>Non renseigné</b>";
        }else{
            if($this->inscription->getCoTutelle()){
                $pays = $this->inscription->getPaysCoTutelle() ? $this->inscription->getPaysCoTutelle()->getLibelle() : "<b>Non renseigné</b>";
                return "Oui <br> <ul><li><b>Pays concerné : </b>".$pays."</li></ul>";
            }else{
                return "Non";
            }
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getCoDirectionDemandeeLibelle()
    {
        if($this->inscription->getCoDirection() === null){
            return "<b>Non renseigné</b>";
        }else{
            if($this->inscription->getCoDirection()) {
                $coDirecteur = $this->inscription->getCoDirecteur() ?
                    $this->inscription->getCoDirecteur()->getCivilite() . " " . $this->inscription->getCoDirecteur()->getNomComplet() :
                    $this->inscription->getNomCodirecteurThese() . " " . $this->inscription->getPrenomCodirecteurThese();
                return $coDirecteur;
            }else{
                return "Non";
            }
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getCoEncadrementLibelle()
    {
        if($this->inscription->getCoEncadrement() === null){
            return "<b>Non renseigné</b>";
        }else{
            return $this->inscription->getCoEncadrement() ? "Oui" : "Non";
        }
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getFonctionDirecteurLibelle(): ?string
    {
        return $this->inscription->getFonctionDirecteurLibelle();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getUniteRechercheCoDirecteurLibelle(): ?string
    {
        return $this->inscription->getUniteRechercheCoDirecteurLibelle();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEmailDirecteurThese(): ?string
    {
        return $this->inscription->getEmailDirecteurThese();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEtablissementRattachementCoDirecteurLibelle(): ?string
    {
        return $this->inscription->getEtablissementRattachementCoDirecteurLibelle();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getComposanteDoctoratLibelle(): ?string
    {
        return $this->inscription->getComposanteDoctoratLibelle();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEtablissementLaboratoireRecherche(): ?string
    {
        return $this->inscription->getEtablissementLaboratoireRecherche();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDisciplineDoctorat(): ?string
    {
        return $this->inscription->getDisciplineDoctorat();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getTitreThese(): ?string
    {
        return $this->inscription->getTitreThese();
    }

    /**
     * @noinspection PhpUnused (il s'agit d'une méthode utilisée par les macros)
     */
    public function getFonctionCoDirecteurLibelle(): ?string
    {
        return $this->inscription->getFonctionCoDirecteurLibelle();
    }



}