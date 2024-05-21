<?php
namespace Admission\Entity\Db;

use Application\Entity\Db\Pays;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Etudiant implements HistoriqueAwareInterface{
    use HistoriqueAwareTrait;

    /**
     * @var string|null
     */
    private $sexe;

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
     * @var Pays|null
     */
    private $nationalite;

    /**
     * @var string|null
     */
    private $ine;

    /**
     * @var string|null
     */
    private $numeroCandidat;

    /**
     * @var Pays
     */
    private $adresseCodePays;

    /**
     * @var string|null
     */
    private $adresseLigne1Etage;

    /**
     * @var string|null
     */
    private $adresseLigne2Batiment;

    /**
     * @var string|null
     */
    private $adresseLigne3voie;

    /**
     * @var string|null
     */
    private $adresseLigne4Complement;

    /**
     * @var string|null
     */
    private $codeCommuneNaissance;

    /**
     * @var string|null
     */
    private $libelleCommuneNaissance;

    /**
     * @var int|null
     */
    private $adresseCodePostal;

    /**
     * @var int|null
     */
    private $adresseCodeCommune;

    /**
     * @var string|null
     */
    private $adresseNomCommune;

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
     * @var int|null
     */
    private $niveauEtude;

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
     * @var integer|null
     */
    private $typeDiplomeAutre;

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
     * @var int
     */
    private $id;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * @var Pays
     */
    private $paysNaissance;

    /**
     * @var Collection
     */
    private $verificationEtudiant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->verificationEtudiant = new ArrayCollection();
    }

    /**
     * Set sexe.
     *
     * @param string|null $sexe
     *
     * @return Etudiant
     */
    public function setSexe($sexe = null)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe.
     *
     * @return string|null
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set nomUsuel.
     *
     * @param string|null $nomUsuel
     *
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * Set nationalite.
     *
     * @param Pays|null $nationalite
     *
     * @return Etudiant
     */
    public function setNationalite($nationalite = null)
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
     * Set ine.
     *
     * @param string|null $ine
     *
     * @return Etudiant
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
     * Set numeroCandidat.
     *
     * @param string|null $numeroCandidat
     *
     * @return Etudiant
     */
    public function setNumeroCandidat($numeroCandidat = null)
    {
        $this->numeroCandidat = $numeroCandidat;

        return $this;
    }

    /**
     * Get numeroCandidat.
     *
     * @return string|null
     */
    public function getNumeroCandidat()
    {
        return $this->numeroCandidat;
    }

    /**
     * Set adresseCodePays.
     *
     * @param Pays $adresseCodePays
     *
     * @return Etudiant
     */
    public function setAdresseCodePays($adresseCodePays = null)
    {
        $this->adresseCodePays = $adresseCodePays;

        return $this;
    }

    /**
     * Get adresseCodePays.
     *
     * @return Pays|null
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
     * @return Etudiant
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
     * Set adresseLigne2Batiment.
     *
     * @param string|null $adresseLigne2Batiment
     *
     * @return Etudiant
     */
    public function setAdresseLigne2Batiment($adresseLigne2Batiment = null)
    {
        $this->adresseLigne2Batiment = $adresseLigne2Batiment;

        return $this;
    }

    /**
     * Get adresseLigne2Batiment.
     *
     * @return string|null
     */
    public function getAdresseLigne2Batiment()
    {
        return $this->adresseLigne2Batiment;
    }

    /**
     * Set adresseLigne3voie.
     *
     * @param string|null $adresseLigne3voie
     *
     * @return Etudiant
     */
    public function setAdresseLigne3voie($adresseLigne3voie = null)
    {
        $this->adresseLigne3voie = $adresseLigne3voie;

        return $this;
    }

    /**
     * Get adresseLigne3voie.
     *
     * @return string|null
     */
    public function getAdresseLigne3voie()
    {
        return $this->adresseLigne3voie;
    }

    /**
     * Set adresseLigne4Complement.
     *
     * @param string|null $adresseLigne4Complement
     *
     * @return Etudiant
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
     * @return Etudiant
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
     * @param int|null $adresseCodeCommune
     *
     * @return Etudiant
     */
    public function setAdresseCodeCommune($adresseCodeCommune = null)
    {
        $this->adresseCodeCommune = $adresseCodeCommune;

        return $this;
    }

    /**
     * Get adresseCodeCommune.
     *
     * @return int|null
     */
    public function getAdresseCodeCommune()
    {
        return $this->adresseCodeCommune;
    }

    /**
     * Set adresseNomCommune.
     *
     * @param int|null $adresseNomCommune
     *
     * @return Etudiant
     */
    public function setAdresseNomCommune($adresseNomCommune = null)
    {
        $this->adresseNomCommune = $adresseNomCommune;

        return $this;
    }

    /**
     * Get adresseNomCommune.
     *
     * @return string|null
     */
    public function getAdresseNomCommune()
    {
        return $this->adresseNomCommune;
    }

    /**
     * Set adresseCpVilleEtrangere.
     *
     * @param string|null $adresseCpVilleEtrangere
     *
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * Set niveauEtude.
     *
     * @param int|null $niveauEtude
     *
     * @return Etudiant
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

    /**
     * Set intituleDuDiplomeNational.
     *
     * @param string|null $intituleDuDiplomeNational
     *
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * Set typeDiplomeAutre.
     *
     * @param integer|null $typeDiplomeAutre
     *
     * @return Etudiant
     */
    public function setTypeDiplomeAutre($typeDiplomeAutre = null)
    {
        $this->typeDiplomeAutre = $typeDiplomeAutre;

        return $this;
    }

    /**
     * Get typeDiplomeAutre.
     *
     * @return integer|null
     */
    public function getTypeDiplomeAutre()
    {
        return $this->typeDiplomeAutre;
    }

    /**
     * Set intituleDuDiplomeAutre.
     *
     * @param string|null $intituleDuDiplomeAutre
     *
     * @return Etudiant
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
     * @return Etudiant
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
     * @return Etudiant
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return Etudiant
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
     * Set codeCommuneNaissance.
     *
     * @param string|null $codeCommuneNaissance
     *
     * @return Etudiant
     */
    public function setCodeCommuneNaissance($codeCommuneNaissance = null)
    {
        $this->codeCommuneNaissance = $codeCommuneNaissance;

        return $this;
    }

    /**
     * Get codeCommuneNaissance.
     *
     * @return string|null
     */
    public function getCodeCommuneNaissance()
    {
        return $this->codeCommuneNaissance;
    }

    /**
     * Set libelleCommuneNaissance.
     *
     * @param string|null $libelleCommuneNaissance
     *
     * @return Etudiant
     */
    public function setLibelleCommuneNaissance($libelleCommuneNaissance = null)
    {
        $this->libelleCommuneNaissance = $libelleCommuneNaissance;

        return $this;
    }

    /**
     * Get libelleCommuneNaissance.
     *
     * @return string|null
     */
    public function getLibelleCommuneNaissance()
    {
        return $this->libelleCommuneNaissance;
    }

    /**
     * Set paysNaissance.
     *
     * @param Pays|null $paysNaissance
     *
     * @return Etudiant
     */
    public function setPaysNaissance(Pays $paysNaissance = null)
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
     * Add verificationEtudiant.
     */
    public function addVerificationEtudiant(Collection $verificationEtudiants)
    {
//        var_dump($verificationEtudiants);
//        foreach ($verificationEtudiants as $vE) {
//            if (!$this->verificationEtudiant->contains($vE)) {
//                $this->verificationEtudiant->add($vE);
//            }
//        }

        return $this;
    }

    /**
     * Remove verificationEtudiant.
     */
    public function removeVerificationEtudiant(Collection $verificationEtudiants)
    {
        foreach ($verificationEtudiants as $vE) {
            $this->verificationEtudiant->removeElement($vE);
        }
    }

    /**
     * Get verificationEtudiant.
     *
     * @return Collection
     */
    public function getVerificationEtudiant(): ArrayCollection|Collection
    {
        return $this->verificationEtudiant;
    }

    /** Pour macro ****************************************************************************************************/

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDenominationEtudiant() : string
    {
        return $this->getCivilite()." ".$this->getNomUsuel()." ".$this->getPrenom();
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getDateNaissanceFormat()
    {
        return $this->getDateNaissance() ? $this->getDateNaissance()->format("d/m/Y") : null;
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getNationaliteLibelle()
    {
        return $this->getNationalite() ? $this->getNationalite()->getLibelleNationalite() : null;
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getPaysNaissanceLibelle()
    {
        return $this->getPaysNaissance() ? $this->getPaysNaissance()->getLibelle() : null;
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getSituationHandicapLibelle()
    {
        if(empty($this->getSituationHandicap())){
            return "<b>Non renseigné</b>";
        }else{
            return $this->getSituationHandicap() ? "Oui" : "Non";
        }
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getNiveauEtudeInformations(){
        if($this->niveauEtude == 1){
            return "Diplôme national tel que le master";
        }else if($this->niveauEtude == 2){
            return "Diplôme autre qu'un diplôme national - à titre dérogatoire";
        }else{
            return "<b>Non renseigné</b>";
        }
    }
}
