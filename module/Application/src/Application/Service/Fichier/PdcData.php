<?php

namespace Application\Service\Fichier;

class MembreData {
    private $denomination;
    private $qualite;
    private $etablissement;
    private $role;

    /**
     * @return mixed
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * @param mixed $denomination
     * @return MembreData
     */
    public function setDenomination($denomination)
    {
        $this->denomination = $denomination;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQualite()
    {
        return $this->qualite;
    }

    /**
     * @param mixed $qualite
     * @return MembreData
     */
    public function setQualite($qualite)
    {
        $this->qualite = $qualite;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param mixed $etablissement
     * @return MembreData
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     * @return MembreData
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }
}

/** Les informations ici sont des chaines de caractères */
class PdcData {

    /** @var string */
    private $titre;
    /** @var string */
    private $specialite;
    /** @var string */
    private $etablissement;
    /** @var string */
    private $doctorant;
    /** @var string */
    private $date;

    /** @var bool */
    private $cotutuelle;
    /** @var string */
    private $cotutuelleLibelle;
    /** @var string */
    private $cotutuellePays;

    /** @var bool */
    private $associe;
    /** @var string */
    private $logoAssocie;
    /** @var string */
    private $libelleAssocie;


    /** @var MembreData[] */
    private $directeurs;
    /** @var MembreData[] */
    private $rapporteurs;
    /** @var MembreData[] */
    private $membres;

    /** @var MembreData[] */
    private $acteursEnCouverture;

    /** @var string */
    private $logoCOMUE;
    /** @var string */
    private $logoEtablissement;
    /** @var string */
    private $logoEcoleDoctorale;
    /** @var string */
    private $logoUniteRecherche;

    /** @var string */
    private $listing;
    /** @var string */
    private $uniteRecherche;

    /**
     * @return string
     */
    public function getUniteRecherche()
    {
        return $this->uniteRecherche;
    }

    /**
     * @param string $uniteRecherche
     * @return PdcData
     */
    public function setUniteRecherche($uniteRecherche)
    {
        $this->uniteRecherche = $uniteRecherche;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * @param string $titre
     * @return PdcData
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecialite()
    {
        return $this->specialite;
    }

    /**
     * @param string $specialite
     * @return PdcData
     */
    public function setSpecialite($specialite)
    {
        $this->specialite = $specialite;
        return $this;
    }

    /**
     * @return string
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @param string $etablissement
     * @return PdcData
     */
    public function setEtablissement($etablissement)
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @return string
     */
    public function getDoctorant()
    {
        return $this->doctorant;
    }

    /**
     * @param string $doctorant
     * @return PdcData
     */
    public function setDoctorant($doctorant)
    {
        $this->doctorant = $doctorant;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return PdcData
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCotutuelle()
    {
        return $this->cotutuelle;
    }

    /**
     * @param bool $cotutuelle
     * @return PdcData
     */
    public function setCotutuelle($cotutuelle)
    {
        $this->cotutuelle = $cotutuelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getCotutuelleLibelle()
    {
        return $this->cotutuelleLibelle;
    }

    /**
     * @param string $cotutuelleLibelle
     * @return PdcData
     */
    public function setCotutuelleLibelle($cotutuelleLibelle)
    {
        $this->cotutuelleLibelle = $cotutuelleLibelle;
        return $this;
    }

    /**
     * @return string
     */
    public function getCotutuellePays()
    {
        return $this->cotutuellePays;
    }

    /**
     * @param string $cotutuellePays
     * @return PdcData
     */
    public function setCotutuellePays($cotutuellePays)
    {
        $this->cotutuellePays = $cotutuellePays;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAssocie()
    {
        return $this->associe;
    }

    /**
     * @param bool $associe
     * @return PdcData
     */
    public function setAssocie($associe)
    {
        $this->associe = $associe;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoAssocie()
    {
        return $this->logoAssocie;
    }

    /**
     * @param string $logoAssocie
     * @return PdcData
     */
    public function setLogoAssocie($logoAssocie)
    {
        $this->logoAssocie = $logoAssocie;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibelleAssocie()
    {
        return $this->libelleAssocie;
    }

    /**
     * @param string $libelleAssocie
     * @return PdcData
     */
    public function setLibelleAssocie($libelleAssocie)
    {
        $this->libelleAssocie = $libelleAssocie;
        return $this;
    }



    /**
     * @return MembreData[]
     */
    public function getDirecteurs()
    {
        return $this->directeurs;
    }

    /**
     * @param MembreData $directeur
     * @return PdcData
     */
    public function addDirecteur($directeur)
    {
        $this->directeurs[] = $directeur;
        return $this;
    }

    /**
     * @return MembreData[]
     */
    public function getRapporteurs()
    {
        return $this->rapporteurs;
    }

    /**
     * @param MembreData $rapporteur
     * @return PdcData
     */
    public function addRapporteur($rapporteur)
    {
        $this->rapporteurs[] = $rapporteur;
        return $this;
    }

    /**
     * @return MembreData[]
     */
    public function getMembres()
    {
        return $this->membres;
    }

    /**
     * @param MembreData $membre
     * @return PdcData
     */
    public function addMembre($membre)
    {
        $this->membres[] = $membre;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoCOMUE()
    {
        return $this->logoCOMUE;
    }

    /**
     * @param string $logoCOMUE
     * @return PdcData
     */
    public function setLogoCOMUE($logoCOMUE)
    {
        $this->logoCOMUE = $logoCOMUE;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoEtablissement()
    {
        return $this->logoEtablissement;
    }

    /**
     * @param string $logoEtablissement
     * @return PdcData
     */
    public function setLogoEtablissement($logoEtablissement)
    {
        $this->logoEtablissement = $logoEtablissement;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoEcoleDoctorale()
    {
        return $this->logoEcoleDoctorale;
    }

    /**
     * @param string $logoEcoleDoctorale
     * @return PdcData
     */
    public function setLogoEcoleDoctorale($logoEcoleDoctorale)
    {
        $this->logoEcoleDoctorale = $logoEcoleDoctorale;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoUniteRecherche()
    {
        return $this->logoUniteRecherche;
    }

    /**
     * @param string $logoUniteRecherche
     * @return PdcData
     */
    public function setLogoUniteRecherche($logoUniteRecherche)
    {
        $this->logoUniteRecherche = $logoUniteRecherche;
        return $this;
    }

    /**
     * @return string
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param string $listing
     */
    public function setListing($listing)
    {
        $this->listing = $listing;
    }

    public function getWarnings() {
        $warnings = [];

        //logos
        if (!$this->getLogoCOMUE())                 $warnings[] = "Logo de la COMUE absent";
        if (!$this->getLogoEcoleDoctorale())        $warnings[] = "Logo de l'école doctorale absent";
        if (!$this->getLogoEtablissement())         $warnings[] = "Logo de l'établissement absent";
        if (!$this->getLogoUniteRecherche())        $warnings[] = "Logo de l'unité de recherche absent";

        if ($this->isAssocie() && !$this->getLogoAssocie()) $warnings[] = "Logo de l'établissement associé absent";

        //infos generales
        if(!$this->getTitre())                      $warnings[] = "Titre de thèse manquant";
        if(!$this->getSpecialite())                 $warnings[] = "Spécialité manquante";
        if(!$this->getEtablissement())              $warnings[] = "Établissement d'encadrement manquant";
        if(!$this->getUniteRecherche())             $warnings[] = "Unité de recherche d'encadrement manquant";
        if(!$this->getDoctorant())                  $warnings[] = "Les informations sur le doctorant sont manquantes";

        //soutenance
        if(!$this->getDate())                       $warnings[] = "La date de soutenance est manquante";
        if(count($this->getRapporteurs())<2)        $warnings[] = "Nombre de rapporteurs trop faible (".count($this->getRapporteurs()).")";
        if(count($this->getDirecteurs())<1)         $warnings[] = "Nombre de directeurs trop faible (".count($this->getDirecteurs()).")";

        //membres du jury
        if ($this->getRapporteurs()) {
            /** @var MembreData $rapporteur */
            foreach($this->getRapporteurs() as $rapporteur) {
                if (!$rapporteur->getDenomination())    $warnings[] = "Un rapporteur ne possèdent pas de nom.";
                if (!$rapporteur->getEtablissement())   $warnings[] = "Établissement absent pour le rapporteur (".$rapporteur->getDenomination().").";
                if (!$rapporteur->getQualite())         $warnings[] = "Qualité absente pour le rapporteur (".$rapporteur->getDenomination().").";
            }
        }
        /** @var MembreData $membre */
        if ($this->getMembres()) {
            foreach ($this->getMembres() as $membre) {
                if (!$membre->getDenomination()) $warnings[] = "Un membre ne possèdent pas de nom.";
                if (!$membre->getEtablissement()) $warnings[] = "Établissement absent pour le membre (" . $membre->getDenomination() . ").";
                if (!$membre->getQualite()) $warnings[] = "Qualité absente pour le membre (" . $membre->getDenomination() . ").";
            }
        }
        /** @var MembreData $directeur */
        if ($this->getDirecteurs()) {
            foreach ($this->getDirecteurs() as $directeur) {
                if (!$directeur->getDenomination()) $warnings[] = "Un directeur ne possèdent pas de nom.";
                if (!$directeur->getEtablissement()) $warnings[] = "Établissement absent pour le directeur (" . $directeur->getDenomination() . ").";
                if (!$directeur->getQualite()) $warnings[] = "Qualité absente pour le directeur (" . $directeur->getDenomination() . ").";
            }
        }
        //cotutuelle :: aucun warning ca l'info sert à détecter la cotutelle


        return $warnings;
    }

    /**
     * @return MembreData[]
     */
    public function getActeursEnCouverture()
    {
        return $this->acteursEnCouverture;
    }

    /**
     * @param MembreData $acteur
     * @return PdcData
     */
    public function addActeurEnCouverture($acteur)
    {
        $this->acteursEnCouverture[] = $acteur;
        return $this;
    }

}