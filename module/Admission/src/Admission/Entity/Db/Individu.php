<?php
namespace Admission\Entity\Db;

use Application\Entity\Db\Pays;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Individu implements HistoriqueAwareInterface{
    use HistoriqueAwareTrait;

    /**
     * @var string|null
     */
    private $civilite;

    /**
     * @var string|null
     */
    private $nomUsuel;

    /**
     * @var string|null
     */
    private $nomFamille;

    /**
     * @var string|null
     */
    private $prenom;

    /**
     * @var string|null
     */
    private $prenom2;

    /**
     * @var string|null
     */
    private $prenom3;

    /**
     * @var \DateTime|null
     */
    private $dateNaissance;

    /**
     * @var string|null
     */
    private $villeNaissance;

    /**
     * @var string|null
     */
    private $codeNationalite;

    /**
     * @var string|null
     */
    private $ine;

    /**
     * @var string|null
     */
    private $adresseCodePays;

    /**
     * @var string|null
     */
    private $adresseLigne1Etage;

    /**
     * @var string|null
     */
    private $adresseLigne2Etage;

    /**
     * @var string|null
     */
    private $adresseLigne3Batiment;

    /**
     * @var string|null
     */
    private $adresseLigne3Bvoie;

    /**
     * @var string|null
     */
    private $adresseLigne4Complement;

    /**
     * @var int|null
     */
    private $adresseCodePostal;

    /**
     * @var string|null
     */
    private $adresseCodeCommune;

    /**
     * @var string|null
     */
    private $adresseCpVilleEtrangere;

    /**
     * @var string|null
     */
    private $numeroTelephone1;

    /**
     * @var string|null
     */
    private $numeroTelephone2;

    /**
     * @var string|null
     */
    private $courriel;

    /**
     * @var bool|null
     */
    private $situationHandicap;

    /**
     * @var string|null
     */
    private $intituleDuDiplome;

    /**
     * @var int|null
     */
    private $anneeDobtentionDiplome;

    /**
     * @var string|null
     */
    private $etablissementDobtentionDiplome;

    /**
     * @var bool|null
     */
    private $typeDiplomeAutre;

    /**
     * @var ?int
     */
    private ?int $id = null;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * @var Pays
     */
    private $paysNaissance = null;

    /**
     * @var string|null
     */
    private $intituleDuDiplomeNational;

    /**
     * @var int|null
     */
    private $anneeDobtentionDiplomeNational;

    /**
     * @var string|null
     */
    private $etablissementDobtentionDiplomeNational;

    /**
     * @var string|null
     */
    private $intituleDuDiplomeAutre;

    /**
     * @var int|null
     */
    private $anneeDobtentionDiplomeAutre;

    /**
     * @var string|null
     */
    private $etablissementDobtentionDiplomeAutre;

    /**
     * @var Pays
     */
    private $nationalite;

    /**
     * @var int|null
     */
    private $niveauEtude;

    /**
     * Set civilite.
     *
     * @param string|null $civilite
     *
     * @return Individu
     */
    public function setCivilite($civilite = null)
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * Get civilite.
     *
     * @return string|null
     */
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * Set nomUsuel.
     *
     * @param string|null $nomUsuel
     *
     * @return Individu
     */
    public function setNomUsuel($nomUsuel = null)
    {
        $this->nomUsuel = $nomUsuel;

        return $this;
    }

    /**
     * Get nomUsuel.
     *
     * @return string|null
     */
    public function getNomUsuel()
    {
        return $this->nomUsuel;
    }

    /**
     * Set nomFamille.
     *
     * @param string|null $nomFamille
     *
     * @return Individu
     */
    public function setNomFamille($nomFamille = null)
    {
        $this->nomFamille = $nomFamille;

        return $this;
    }

    /**
     * Get nomFamille.
     *
     * @return string|null
     */
    public function getNomFamille()
    {
        return $this->nomFamille;
    }

    /**
     * Set prenom.
     *
     * @param string|null $prenom
     *
     * @return Individu
     */
    public function setPrenom($prenom = null)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom.
     *
     * @return string|null
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set prenom2.
     *
     * @param string|null $prenom2
     *
     * @return Individu
     */
    public function setPrenom2($prenom2 = null)
    {
        $this->prenom2 = $prenom2;

        return $this;
    }

    /**
     * Get prenom2.
     *
     * @return string|null
     */
    public function getPrenom2()
    {
        return $this->prenom2;
    }

    /**
     * Set prenom3.
     *
     * @param string|null $prenom3
     *
     * @return Individu
     */
    public function setPrenom3($prenom3 = null)
    {
        $this->prenom3 = $prenom3;

        return $this;
    }

    /**
     * Get prenom3.
     *
     * @return string|null
     */
    public function getPrenom3()
    {
        return $this->prenom3;
    }

    /**
     * Set dateNaissance.
     *
     * @param \DateTime|null $dateNaissance
     *
     * @return Individu
     */
    public function setDateNaissance($dateNaissance = null)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance.
     *
     * @return \DateTime|null
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Set villeNaissance.
     *
     * @param string|null $villeNaissance
     *
     * @return Individu
     */
    public function setVilleNaissance($villeNaissance = null)
    {
        $this->villeNaissance = $villeNaissance;

        return $this;
    }

    /**
     * Get villeNaissance.
     *
     * @return string|null
     */
    public function getVilleNaissance()
    {
        return $this->villeNaissance;
    }

    /**
     * Set codeNationalite.
     *
     * @param string|null $codeNationalite
     *
     * @return Individu
     */
    public function setCodeNationalite($codeNationalite = null)
    {
        $this->codeNationalite = $codeNationalite;

        return $this;
    }

    /**
     * Get codeNationalite.
     *
     * @return string|null
     */
    public function getCodeNationalite()
    {
        return $this->codeNationalite;
    }

    /**
     * Set ine.
     *
     * @param string|null $ine
     *
     * @return Individu
     */
    public function setIne($ine = null)
    {
        $this->ine = $ine;

        return $this;
    }

    /**
     * Get ine.
     *
     * @return string|null
     */
    public function getIne()
    {
        return $this->ine;
    }

    /**
     * Set adresseCodePays.
     *
     * @param string|null $adresseCodePays
     *
     * @return Individu
     */
    public function setAdresseCodePays($adresseCodePays = null)
    {
        $this->adresseCodePays = $adresseCodePays;

        return $this;
    }

    /**
     * Get adresseCodePays.
     *
     * @return string|null
     */
    public function getAdresseCodePays()
    {
        return $this->adresseCodePays;
    }

    /**
     * Set adresseLigne1Etage.
     *
     * @param string|null $adresseLigne1Etage
     *
     * @return Individu
     */
    public function setAdresseLigne1Etage($adresseLigne1Etage = null)
    {
        $this->adresseLigne1Etage = $adresseLigne1Etage;

        return $this;
    }

    /**
     * Get adresseLigne1Etage.
     *
     * @return string|null
     */
    public function getAdresseLigne1Etage()
    {
        return $this->adresseLigne1Etage;
    }

    /**
     * Set adresseLigne2Etage.
     *
     * @param string|null $adresseLigne2Etage
     *
     * @return Individu
     */
    public function setAdresseLigne2Etage($adresseLigne2Etage = null)
    {
        $this->adresseLigne2Etage = $adresseLigne2Etage;

        return $this;
    }

    /**
     * Get adresseLigne2Etage.
     *
     * @return string|null
     */
    public function getAdresseLigne2Etage()
    {
        return $this->adresseLigne2Etage;
    }

    /**
     * Set adresseLigne3Batiment.
     *
     * @param string|null $adresseLigne3Batiment
     *
     * @return Individu
     */
    public function setAdresseLigne3Batiment($adresseLigne3Batiment = null)
    {
        $this->adresseLigne3Batiment = $adresseLigne3Batiment;

        return $this;
    }

    /**
     * Get adresseLigne3Batiment.
     *
     * @return string|null
     */
    public function getAdresseLigne3Batiment()
    {
        return $this->adresseLigne3Batiment;
    }

    /**
     * Set adresseLigne3Bvoie.
     *
     * @param string|null $adresseLigne3Bvoie
     *
     * @return Individu
     */
    public function setAdresseLigne3Bvoie($adresseLigne3Bvoie = null)
    {
        $this->adresseLigne3Bvoie = $adresseLigne3Bvoie;

        return $this;
    }

    /**
     * Get adresseLigne3Bvoie.
     *
     * @return string|null
     */
    public function getAdresseLigne3Bvoie()
    {
        return $this->adresseLigne3Bvoie;
    }

    /**
     * Set adresseLigne4Complement.
     *
     * @param string|null $adresseLigne4Complement
     *
     * @return Individu
     */
    public function setAdresseLigne4Complement($adresseLigne4Complement = null)
    {
        $this->adresseLigne4Complement = $adresseLigne4Complement;

        return $this;
    }

    /**
     * Get adresseLigne4Complement.
     *
     * @return string|null
     */
    public function getAdresseLigne4Complement()
    {
        return $this->adresseLigne4Complement;
    }

    /**
     * Set adresseCodePostal.
     *
     * @param int|null $adresseCodePostal
     *
     * @return Individu
     */
    public function setAdresseCodePostal($adresseCodePostal = null)
    {
        $this->adresseCodePostal = $adresseCodePostal;

        return $this;
    }

    /**
     * Get adresseCodePostal.
     *
     * @return int|null
     */
    public function getAdresseCodePostal()
    {
        return $this->adresseCodePostal;
    }

    /**
     * Set adresseCodeCommune.
     *
     * @param string|null $adresseCodeCommune
     *
     * @return Individu
     */
    public function setAdresseCodeCommune($adresseCodeCommune = null)
    {
        $this->adresseCodeCommune = $adresseCodeCommune;

        return $this;
    }

    /**
     * Get adresseCodeCommune.
     *
     * @return string|null
     */
    public function getAdresseCodeCommune()
    {
        return $this->adresseCodeCommune;
    }

    /**
     * Set adresseCpVilleEtrangere.
     *
     * @param string|null $adresseCpVilleEtrangere
     *
     * @return Individu
     */
    public function setAdresseCpVilleEtrangere($adresseCpVilleEtrangere = null)
    {
        $this->adresseCpVilleEtrangere = $adresseCpVilleEtrangere;

        return $this;
    }

    /**
     * Get adresseCpVilleEtrangere.
     *
     * @return string|null
     */
    public function getAdresseCpVilleEtrangere()
    {
        return $this->adresseCpVilleEtrangere;
    }

    /**
     * Set numeroTelephone1.
     *
     * @param string|null $numeroTelephone1
     *
     * @return Individu
     */
    public function setNumeroTelephone1($numeroTelephone1 = null)
    {
        $this->numeroTelephone1 = $numeroTelephone1;

        return $this;
    }

    /**
     * Get numeroTelephone1.
     *
     * @return string|null
     */
    public function getNumeroTelephone1()
    {
        return $this->numeroTelephone1;
    }

    /**
     * Set numeroTelephone2.
     *
     * @param string|null $numeroTelephone2
     *
     * @return Individu
     */
    public function setNumeroTelephone2($numeroTelephone2 = null)
    {
        $this->numeroTelephone2 = $numeroTelephone2;

        return $this;
    }

    /**
     * Get numeroTelephone2.
     *
     * @return string|null
     */
    public function getNumeroTelephone2()
    {
        return $this->numeroTelephone2;
    }

    /**
     * Set courriel.
     *
     * @param string|null $courriel
     *
     * @return Individu
     */
    public function setCourriel($courriel = null)
    {
        $this->courriel = $courriel;

        return $this;
    }

    /**
     * Get courriel.
     *
     * @return string|null
     */
    public function getCourriel()
    {
        return $this->courriel;
    }

    /**
     * Set situationHandicap.
     *
     * @param bool|null $situationHandicap
     *
     * @return Individu
     */
    public function setSituationHandicap($situationHandicap = null)
    {
        $this->situationHandicap = $situationHandicap;

        return $this;
    }

    /**
     * Get situationHandicap.
     *
     * @return bool|null
     */
    public function getSituationHandicap()
    {
        return $this->situationHandicap;
    }

    /**
     * Set intituleDuDiplome.
     *
     * @param string|null $intituleDuDiplome
     *
     * @return Individu
     */
    public function setIntituleDuDiplome($intituleDuDiplome = null)
    {
        $this->intituleDuDiplome = $intituleDuDiplome;

        return $this;
    }

    /**
     * Get intituleDuDiplome.
     *
     * @return string|null
     */
    public function getIntituleDuDiplome()
    {
        return $this->intituleDuDiplome;
    }

    /**
     * Set anneeDobtentionDiplome.
     *
     * @param int|null $anneeDobtentionDiplome
     *
     * @return Individu
     */
    public function setAnneeDobtentionDiplome($anneeDobtentionDiplome = null)
    {
        $this->anneeDobtentionDiplome = $anneeDobtentionDiplome;

        return $this;
    }

    /**
     * Get anneeDobtentionDiplome.
     *
     * @return int|null
     */
    public function getAnneeDobtentionDiplome()
    {
        return $this->anneeDobtentionDiplome;
    }

    /**
     * Set etablissementDobtentionDiplome.
     *
     * @param string|null $etablissementDobtentionDiplome
     *
     * @return Individu
     */
    public function setEtablissementDobtentionDiplome($etablissementDobtentionDiplome = null)
    {
        $this->etablissementDobtentionDiplome = $etablissementDobtentionDiplome;

        return $this;
    }

    /**
     * Get etablissementDobtentionDiplome.
     *
     * @return string|null
     */
    public function getEtablissementDobtentionDiplome()
    {
        return $this->etablissementDobtentionDiplome;
    }

    /**
     * Set typeDiplomeAutre.
     *
     * @param bool|null $typeDiplomeAutre
     *
     * @return Individu
     */
    public function setTypeDiplomeAutre($typeDiplomeAutre = null)
    {
        $this->typeDiplomeAutre = $typeDiplomeAutre;

        return $this;
    }

    /**
     * Get typeDiplomeAutre.
     *
     * @return bool|null
     */
    public function getTypeDiplomeAutre()
    {
        return $this->typeDiplomeAutre;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set paysNaissance.
     *
     * @param Pays|null $paysNaissance
     *
     * @return Individu
     */
    public function setPaysNaissance(?Pays $paysNaissance): Individu
    {
        $this->paysNaissance = $paysNaissance;

        return $this;
    }

    /**
     * Get paysNaissance.
     *
     * @return Pays|null
     */
    public function getPaysNaissance()
    {
        return $this->paysNaissance;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return Individu
     */
    public function setAdmission(Admission $admission = null)
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission.
     *
     * @return Admission|null
     */
    public function getAdmission()
    {
        return $this->admission;
    }

    /**
     * Set intituleDuDiplomeNational.
     *
     * @param string|null $intituleDuDiplomeNational
     *
     * @return Individu
     */
    public function setIntituleDuDiplomeNational($intituleDuDiplomeNational = null)
    {
        $this->intituleDuDiplomeNational = $intituleDuDiplomeNational;

        return $this;
    }

    /**
     * Get intituleDuDiplomeNational.
     *
     * @return string|null
     */
    public function getIntituleDuDiplomeNational()
    {
        return $this->intituleDuDiplomeNational;
    }

    /**
     * Set anneeDobtentionDiplomeNational.
     *
     * @param int|null $anneeDobtentionDiplomeNational
     *
     * @return Individu
     */
    public function setAnneeDobtentionDiplomeNational($anneeDobtentionDiplomeNational = null)
    {
        $this->anneeDobtentionDiplomeNational = $anneeDobtentionDiplomeNational;

        return $this;
    }

    /**
     * Get anneeDobtentionDiplomeNational.
     *
     * @return int|null
     */
    public function getAnneeDobtentionDiplomeNational()
    {
        return $this->anneeDobtentionDiplomeNational;
    }

    /**
     * Set etablissementDobtentionDiplomeNational.
     *
     * @param string|null $etablissementDobtentionDiplomeNational
     *
     * @return Individu
     */
    public function setEtablissementDobtentionDiplomeNational($etablissementDobtentionDiplomeNational = null)
    {
        $this->etablissementDobtentionDiplomeNational = $etablissementDobtentionDiplomeNational;

        return $this;
    }

    /**
     * Get etablissementDobtentionDiplomeNational.
     *
     * @return string|null
     */
    public function getEtablissementDobtentionDiplomeNational()
    {
        return $this->etablissementDobtentionDiplomeNational;
    }

    /**
     * Set intituleDuDiplomeAutre.
     *
     * @param string|null $intituleDuDiplomeAutre
     *
     * @return Individu
     */
    public function setIntituleDuDiplomeAutre($intituleDuDiplomeAutre = null)
    {
        $this->intituleDuDiplomeAutre = $intituleDuDiplomeAutre;

        return $this;
    }

    /**
     * Get intituleDuDiplomeAutre.
     *
     * @return string|null
     */
    public function getIntituleDuDiplomeAutre()
    {
        return $this->intituleDuDiplomeAutre;
    }

    /**
     * Set anneeDobtentionDiplomeAutre.
     *
     * @param int|null $anneeDobtentionDiplomeAutre
     *
     * @return Individu
     */
    public function setAnneeDobtentionDiplomeAutre($anneeDobtentionDiplomeAutre = null)
    {
        $this->anneeDobtentionDiplomeAutre = $anneeDobtentionDiplomeAutre;

        return $this;
    }

    /**
     * Get anneeDobtentionDiplomeAutre.
     *
     * @return int|null
     */
    public function getAnneeDobtentionDiplomeAutre()
    {
        return $this->anneeDobtentionDiplomeAutre;
    }

    /**
     * Set etablissementDobtentionDiplomeAutre.
     *
     * @param string|null $etablissementDobtentionDiplomeAutre
     *
     * @return Individu
     */
    public function setEtablissementDobtentionDiplomeAutre($etablissementDobtentionDiplomeAutre = null)
    {
        $this->etablissementDobtentionDiplomeAutre = $etablissementDobtentionDiplomeAutre;

        return $this;
    }

    /**
     * Get etablissementDobtentionDiplomeAutre.
     *
     * @return string|null
     */
    public function getEtablissementDobtentionDiplomeAutre()
    {
        return $this->etablissementDobtentionDiplomeAutre;
    }

    /**
     * Set nationalite.
     *
     * @param Pays|null $nationalite
     *
     * @return Individu
     */
    public function setNationalite(Pays $nationalite = null)
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    /**
     * Get nationalite.
     *
     * @return Pays|null
     */
    public function getNationalite()
    {
        return $this->nationalite;
    }

    /**
     * Set niveauEtude.
     *
     * @param int|null $niveauEtude
     *
     * @return Individu
     */
    public function setNiveauEtude($niveauEtude = null)
    {
        $this->niveauEtude = $niveauEtude;

        return $this;
    }

    /**
     * Get niveauEtude.
     *
     * @return int|null
     */
    public function getNiveauEtude()
    {
        return $this->niveauEtude;
    }
}
