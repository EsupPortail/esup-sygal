<?php
namespace Admission\Entity\Db;

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
    private $nationalite;

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
     * @var bool|null
     */
    private $niveauEtude;

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
     * @var int
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $admissionId;

    /**
     * @var \Application\Entity\Db\Pays
     */
    private $paysNaissanceId;
    /**
     * Constructor
     */
    public function Construct()
    {
        $this->admissionId = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set nationalite.
     *
     * @param string|null $nationalite
     *
     * @return Individu
     */
    public function setNationalite($nationalite = null)
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    /**
     * Get nationalite.
     *
     * @return string|null
     */
    public function getNationalite()
    {
        return $this->nationalite;
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
     * Set niveauEtude.
     *
     * @param bool|null $niveauEtude
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
     * @return bool|null
     */
    public function getNiveauEtude()
    {
        return $this->niveauEtude;
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
     * Add admissionId.
     *
     * @param \Admission\Entity\Admission $admissionId
     *
     * @return Individu
     */
    public function addAdmissionId(\Admission\Entity\Admission $admissionId)
    {
        $this->admissionId[] = $admissionId;

        return $this;
    }

    /**
     * Remove admissionId.
     *
     * @param \Admission\Entity\Admission $admissionId
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAdmissionId(\Admission\Entity\Admission $admissionId)
    {
        return $this->admissionId->removeElement($admissionId);
    }

    /**
     * Get admissionId.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdmissionId()
    {
        return $this->admissionId;
    }

    /**
     * Set paysNaissanceId.
     *
     * @param \Application\Entity\Db\Pays|null $paysNaissanceId
     *
     * @return Individu
     */
    public function setPaysNaissanceId(\Application\Entity\Db\Pays $paysNaissanceId = null)
    {
        $this->paysNaissanceId = $paysNaissanceId;

        return $this;
    }

    /**
     * Get paysNaissanceId.
     *
     * @return \Application\Entity\Db\Pays|null
     */
    public function getPaysNaissanceId()
    {
        return $this->paysNaissanceId;
    }

    /**
     * Set admissionId.
     *
     * @param \Admission\Entity\Db\Admission|null $admissionId
     *
     * @return Individu
     */
    public function setAdmissionId(\Admission\Entity\Db\Admission $admissionId = null)
    {
        $this->admissionId = $admissionId;

        return $this;
    }
}
