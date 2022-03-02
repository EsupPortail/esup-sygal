<?php

namespace Application\Service\FichierThese;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use DateTime;

class MembreData
{
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
class PdcData
{
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
    /** @var string */
    private $anneeUniversitaire;

    /** @var bool */
    private $cotutuelle;
    /** @var string */
    private $cotutuelleLibelle;
    /** @var string */
    private $cotutuellePays;
    /** @var bool */
    private  $huisClos;

    /** @var bool */
    private $associe;
    /** @var string */
    private $logoAssocie;
    /** @var string */
    private $libelleAssocie;


    /** @var Acteur[] */
    private $directeurs;
    /** @var Acteur[] */
    private $codirecteurs;
    /** @var Acteur[] */
    private $coencadrants;
    /** @var Acteur[] */
    private $rapporteurs;
    /** @var Acteur[] */
    private $membres;
    /** @var Acteur[] */
    private $jury;

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
    /** @var string */
    private $ecoleDoctorale;

    /**
     * PdcData constructor.
     */
    public function __construct()
    {
        $this->acteursEnCouverture = [];
    }

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
    public function getEcoleDoctorale()
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @param string $ecoleDoctorale
     * @return PdcData
     */
    public function setEcoleDoctorale($ecoleDoctorale)
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
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
     * @return Acteur[]
     */
    public function getDirecteurs()
    {
        return $this->directeurs;
    }

    /**
     * @param Acteur[] $directeurs
     * @return PdcData
     */
    public function setDirecteurs($directeurs)
    {
        $this->directeurs = $directeurs;
        return $this;
    }

    /**
     * @return Acteur[]
     */
    public function getCodirecteurs()
    {
        return $this->codirecteurs;
    }

    /**
     * @param Acteur[] $codirecteurs
     * @return PdcData
     */
    public function setCodirecteurs($codirecteurs)
    {
        $this->codirecteurs = $codirecteurs;
        return $this;
    }

    /**
     * @return Acteur[]
     */
    public function getCoencadrants()
    {
        return $this->coencadrants;
    }

    /**
     * @param Acteur[] $coencadrants
     * @return PdcData
     */
    public function setCoencadrants($coencadrants)
    {
        $this->coencadrants = $coencadrants;
        return $this;
    }


    /**
     * @return Acteur[]
     */
    public function getRapporteurs()
    {
        return $this->rapporteurs;
    }

    /**
     * @param Acteur[] $rapporteurs
     * @return PdcData
     */
    public function setRapporteurs($rapporteurs)
    {
        $this->rapporteurs = $rapporteurs;
        return $this;
    }

    /**
     * @return Acteur[]
     */
    public function getMembres()
    {
        return $this->membres;
    }

    /**
     * @param Acteur[] $membres
     * @return PdcData
     */
    public function setMembres($membres)
    {
        $this->membres = $membres;
        return $this;
    }

    /**
     * @return Acteur[]
     */
    public function getJury(): array
    {
        return $this->jury;
    }

    /**
     * @param Acteur[] $jury
     * @return PdcData
     */
    public function setJury(array $jury): PdcData
    {
        $this->jury = $jury;
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
    public function getAnneeUniversitaire()
    {
        return $this->anneeUniversitaire;
    }

    /**
     * @param string $anneeUniversitaire
     * @return PdcData
     */
    public function setAnneeUniversitaire($anneeUniversitaire)
    {
        $this->anneeUniversitaire = $anneeUniversitaire;
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
//        if (!$this->getLogoCOMUE())                 $warnings[] = "Logo de la COMUE absent";
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
        if ($this->getActeursEnCouverture()) {
            /** @var MembreData $acteur */
            foreach($this->getActeursEnCouverture() as $acteur) {
                if (!$acteur->getDenomination())    $warnings[] = "Un ".$acteur->getRole()." ne possèdent pas de nom.";
                if (!$acteur->getEtablissement())   $warnings[] = "Établissement absent pour le ".$acteur->getRole()." (".$acteur->getDenomination().").";
                if (!$acteur->getQualite())         $warnings[] = "Qualité absente pour le ".$acteur->getRole()." (".$acteur->getDenomination().").";
            }
        }
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

    /**
     * @return bool
     */
    public function isHuisClos() : bool
    {
        if ($this->huisClos !== null AND $this->huisClos === true) return true;
        return false;
    }

    /**
     * @param bool $huisClos
     */
    public function setHuisClos(bool $huisClos)
    {
        $this->huisClos = $huisClos;
    }

    /**
     * @return  DateTime|null
     */
    public function getDateFinConfidentialite()
    {
        return $this->dateFinConfidentialite;
    }

    /**
     * @param  DateTime|null $fin
     */
    public function setDateFinConfidentialite($fin)
    {
        $this->dateFinConfidentialite = $fin;
    }

    /**
     * Les signataires du PV de soutenances sont les membres du jury moins les directeurs, les co-directeurs et les co-encadrants
     * @return Individu[]
     */
    public function getSigantaires() : array
    {
        $membres = array_map(function(Acteur $a) { return $a->getIndividu();}, $this->getMembres());
        $directeurs = array_map(function(Acteur $a) { return $a->getIndividu();}, $this->getDirecteurs());
        $codirecteurs = array_map(function(Acteur $a) { return $a->getIndividu();}, $this->getCodirecteurs());
        $coencadrants = array_map(function(Acteur $a) { return $a->getIndividu();}, $this->getCoencadrants());

        $signataires = array_diff($membres, $directeurs, $codirecteurs, $coencadrants);
        return $signataires;
    }
}