<?php

namespace HDR\Service\FichierHDR;

use Acteur\Entity\Db\ActeurHDR;
use DateTime;
use Individu\Entity\Db\Individu;

/** Les informations ici sont des chaines de caractères */
class PdcData
{
    /** @var string */
    private $specialite;
    /** @var string */
    private $etablissement;
    /** @var string */
    private $candidat;
    /** @var string */
    private $date;
    /** @var string */
    private $anneeUniversitaire;
    /** @var bool */
    private  $huisClos;

    /** @var bool */
    private $associe;
    /** @var string */
    private $logoAssocie;
    /** @var string */
    private $libelleAssocie;


    /** @var ActeurHDR[] */
    private $garants;
    private $rapporteurs;
    /** @var ActeurHDR[] */
    private $membres;
    /** @var ActeurHDR[] */
    private $jury;

    /** @var MembreData[] */
    private $acteursEnCouverture;

    /** @var string */
    private $logoEtablissement;
    /** @var string */
    private $logoUniteRecherche;

    private array $listingDirection = [];
    /** @var string */
    private $uniteRecherche;

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
    public function getCandidat()
    {
        return $this->candidat;
    }

    /**
     * @param string $candidat
     * @return PdcData
     */
    public function setCandidat($candidat)
    {
        $this->candidat = $candidat;
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
     * @return ActeurHDR[]
     */
    public function getGarants()
    {
        return $this->garants;
    }

    /**
     * @param ActeurHDR[] $garants
     * @return PdcData
     */
    public function setGarants($garants)
    {
        $this->garants = $garants;
        return $this;
    }

    /**
     * @return ActeurHDR[]
     */
    public function getRapporteurs()
    {
        return $this->rapporteurs;
    }

    /**
     * @param ActeurHDR[] $rapporteurs
     * @return PdcData
     */
    public function setRapporteurs($rapporteurs)
    {
        $this->rapporteurs = $rapporteurs;
        return $this;
    }

    /**
     * @return ActeurHDR[]
     */
    public function getMembres() : array
    {
        return $this->membres;
    }

    /**
     * @param ActeurHDR[] $membres
     * @return PdcData
     */
    public function setMembres($membres)
    {
        $this->membres = $membres;
        return $this;
    }

    /**
     * @return ActeurHDR[]
     */
    public function getJury(): array
    {
        return $this->jury;
    }

    /**
     * @param ActeurHDR[] $jury
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

    public function getDirection(): array
    {
        return $this->listingDirection;
    }

    public function setListingDirection(array $listing): void
    {
        $this->listingDirection = $listing;
    }

    public function getWarnings() {
        $warnings = [];

        //logos
        if (!$this->getLogoEtablissement())         $warnings[] = "Logo de l'établissement absent";
        if (!$this->getLogoUniteRecherche())        $warnings[] = "Logo de l'unité de recherche absent";

        if ($this->isAssocie() && !$this->getLogoAssocie()) $warnings[] = "Logo de l'établissement associé absent";

        //infos generales
        if(!$this->getSpecialite())                 $warnings[] = "Spécialité manquante";
        if(!$this->getEtablissement())              $warnings[] = "Établissement d'encadrement manquant";
        if(!$this->getUniteRecherche())             $warnings[] = "Unité de recherche d'encadrement manquant";
        if(!$this->getCandidat())                  $warnings[] = "Les informations sur le candidat sont manquantes";

        //soutenance
        if(!$this->getDate())                       $warnings[] = "La date de soutenance est manquante";
        if(count($this->getRapporteurs())<2)        $warnings[] = "Nombre de rapporteurs trop faible (".count($this->getRapporteurs()).")";
        if(count($this->getGarants())<1)         $warnings[] = "Nombre de garants trop faible (".count($this->getGarants()).")";

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
     * Les signataires du PV de soutenances sont les membres du jury
     * @return Individu[]
     */
    public function getSignataires() : array
    {
        $membres = array_map(function(ActeurHDR $a) { return $a->getIndividu();}, $this->getMembres());
        //on affiche seulement les rapporteurs qui ne sont pas absent
        $rapporteurs = array_map(function(ActeurHDR $a) { return $a->getMembre() && $a->getMembre()->estMembre() ? $a->getIndividu() : null;}, $this->getRapporteurs());
        $rapporteurs = array_filter($rapporteurs, function($value) {
            return $value !== null;
        });
        $garants = array_map(function(ActeurHDR $a) { return $a->getIndividu();}, $this->getGarants());

        $signataires = array_merge($membres, $rapporteurs, $garants);
        usort($signataires, function(Individu $a, Individu $b) { return $a->getNomComplet() > $b->getNomComplet();});

        return $signataires;
    }
}