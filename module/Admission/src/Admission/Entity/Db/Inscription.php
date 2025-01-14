<?php
namespace Admission\Entity\Db;

use Application\Entity\Db\Discipline;
use Application\Entity\Db\Pays;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Individu\Entity\Db\Individu;
use Soutenance\Entity\Qualite;
use Structure\Entity\Db\ComposanteEnseignement;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Inscription implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var string|null
     */
    private $disciplineDoctorat;

    /**
     * @var string|null
     */
    private $nomDirecteurThese;

    /**
     * @var string|null
     */
    private $nomCodirecteurThese;

    /**
     * @var string|null
     */
    private $titreThese;

    /**
     * @var DateTime|null
     */
    private $dateConfidentialite;

    /**
     * @var bool|null
     */
    private $coTutelle;

    /**
     * @var Pays
     */
    private $paysCoTutelle;

    /**
     * @var bool|null
     */
    private $coEncadrement;

    /**
     * @var bool|null
     */
    private $coDirection;

    /**
     * @var EcoleDoctorale
     */
    private $ecoleDoctorale;

    /**
     * @var string|null
     */
    private $composanteDoctoratLibelle;

    /**
     * @var ComposanteEnseignement
     */
    private $composanteDoctorat;

    /**
     * @var UniteRecherche
     */
    private $uniteRecherche;
    /**
     * @var string|null
     */
    private $etablissementLaboratoireRecherche;
    /**
     * @var Etablissement
     */
    private $etablissementInscription;

    /**
     * @var bool|null
     */
    private $confidentialite;

    /**
     * @var ?int
     */
    private ?int $id = null;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * @var Discipline
     */
    private $specialiteDoctorat;

    /**
     * @var string|null
     */
    private $emailDirecteurThese;

    /**
     * @var string|null
     */
    private $emailCodirecteurThese;

    /**
     * @var string|null
     */
    private $prenomDirecteurThese;

    /**
     * @var string|null
     */
    private $prenomCodirecteurThese;

    /**
     * @var Individu
     */
    private $directeur;

    /**
     * @var Individu
     */
    private $coDirecteur;

    /**
     * @var Qualite
     */
    private $fonctionDirecteurThese;

    /**
     * @var Qualite
     */
    private $fonctionCoDirecteurThese;

    /**
     * @var Collection
     */
    private $verificationInscription;

    /**
     * @var UniteRecherche
     */
    private $uniteRechercheCoDirecteur;

    /**
     * @var Etablissement
     */
    private $etablissementRattachementCoDirecteur;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->verificationInscription = new ArrayCollection();
    }


    /**
     * Set disciplineDoctorat.
     *
     * @param string|null $disciplineDoctorat
     *
     * @return Inscription
     */
    public function setDisciplineDoctorat($disciplineDoctorat = null)
    {
        $this->disciplineDoctorat = $disciplineDoctorat;

        return $this;
    }

    /**
     * Get disciplineDoctorat.
     *
     * @return string|null
     */
    public function getDisciplineDoctorat()
    {
        return $this->disciplineDoctorat;
    }

    /**
     * Set nomDirecteurThese.
     *
     * @param string|null $nomDirecteurThese
     *
     * @return string|null
     */
    public function setNomDirecteurThese($nomDirecteurThese = null)
    {
        $this->nomDirecteurThese = $nomDirecteurThese;

        return $this;
    }

    /**
     * Get nomDirecteurThese.
     *
     * @return string|null
     */
    public function getNomDirecteurThese()
    {
        return $this->nomDirecteurThese;
    }

    /**
     * Set nomCodirecteurThese.
     *
     * @param string|null $nomCodirecteurThese
     *
     * @return string|null
     */
    public function setNomCodirecteurThese($nomCodirecteurThese = null)
    {
        $this->nomCodirecteurThese = $nomCodirecteurThese;

        return $this;
    }

    /**
     * Get nomCodirecteurThese.
     *
     * @return string|null
     */
    public function getNomCodirecteurThese()
    {
        return $this->nomCodirecteurThese;
    }

    /**
     * Set titreThese.
     *
     * @param string|null $titreThese
     *
     * @return Inscription
     */
    public function setTitreThese($titreThese = null)
    {
        $this->titreThese = $titreThese;

        return $this;
    }

    /**
     * Get titreThese.
     *
     * @return string|null
     */
    public function getTitreThese()
    {
        return $this->titreThese;
    }

    /**
     * Set confidentialite.
     *
     * @param bool|null $confidentialite
     *
     * @return Inscription
     */
    public function setConfidentialite($confidentialite = null)
    {
        $this->confidentialite = $confidentialite;

        return $this;
    }

    /**
     * Get confidentialite.
     *
     * @return bool|null
     */
    public function getConfidentialite()
    {
        return $this->confidentialite;
    }

    /**
     * Set dateConfidentialite.
     *
     * @param DateTime|null $dateConfidentialite
     *
     * @return Inscription
     */
    public function setDateConfidentialite($dateConfidentialite = null)
    {
        $this->dateConfidentialite = $dateConfidentialite;

        return $this;
    }

    /**
     * Get dateConfidentialite.
     *
     * @return DateTime|null
     */
    public function getDateConfidentialite()
    {
        return $this->dateConfidentialite;
    }

    /**
     * Set coTutelle.
     *
     * @param bool|null $coTutelle
     *
     * @return Inscription
     */
    public function setCoTutelle($coTutelle = null)
    {
        $this->coTutelle = $coTutelle;

        return $this;
    }

    /**
     * Get coTutelle.
     *
     * @return bool|null
     */
    public function getCoTutelle()
    {
        return $this->coTutelle;
    }


    /**
     * Set paysCoTutelle.
     *
     * @param Pays|null $paysCoTutelle
     *
     * @return Inscription
     */
    public function setPaysCoTutelle(Pays $paysCoTutelle = null)
    {
        $this->paysCoTutelle = $paysCoTutelle;

        return $this;
    }

    /**
     * Get paysCoTutelle.
     *
     * @return Pays|null
     */
    public function getPaysCoTutelle()
    {
        return $this->paysCoTutelle;
    }

    /**
     * Set coEncadrement.
     *
     * @param bool|null $coEncadrement
     *
     * @return Inscription
     */
    public function setCoEncadrement($coEncadrement = null)
    {
        $this->coEncadrement = $coEncadrement;

        return $this;
    }

    /**
     * Get coEncadrement.
     *
     * @return bool|null
     */
    public function getCoEncadrement()
    {
        return $this->coEncadrement;
    }

    /**
     * Set coDirection.
     *
     * @param bool|null $coDirection
     *
     * @return Inscription
     */
    public function setCoDirection($coDirection = null)
    {
        $this->coDirection = $coDirection;

        return $this;
    }

    /**
     * Get coDirection.
     *
     * @return bool|null
     */
    public function getCoDirection()
    {
        return $this->coDirection;
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

    public function getComposanteDoctoratLibelle(): ?string
    {
        return $this->composanteDoctoratLibelle;
    }

    public function setComposanteDoctoratLibelle(?string $composanteDoctoratLibelle): void
    {
        $this->composanteDoctoratLibelle = $composanteDoctoratLibelle;
    }

    /**
     * Set composanteDoctorat.
     *
     * @param ComposanteEnseignement|null $composanteDoctorat
     *
     * @return Inscription
     */
    public function setComposanteDoctorat(ComposanteEnseignement $composanteDoctorat = null)
    {
        $this->composanteDoctorat = $composanteDoctorat;

        return $this;
    }

    /**
     * Get composanteDoctorat.
     *
     * @return ComposanteEnseignement|null
     */
    public function getComposanteDoctorat()
    {
        return $this->composanteDoctorat;
    }

    /**
     * Set ecoleDoctorale.
     *
     * @param EcoleDoctorale|null $ecoleDoctorale
     *
     * @return Inscription
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale = null)
    {
        $this->ecoleDoctorale = $ecoleDoctorale;

        return $this;
    }

    /**
     * Get ecoleDoctorale.
     *
     * @return EcoleDoctorale|null
     */
    public function getEcoleDoctorale()
    {
        return $this->ecoleDoctorale;
    }

    /**
     * Set uniteRecherche.
     *
     * @param UniteRecherche|null $uniteRecherche
     *
     * @return Inscription
     */
    public function setUniteRecherche(UniteRecherche $uniteRecherche = null)
    {
        $this->uniteRecherche = $uniteRecherche;

        return $this;
    }

    /**
     * Get uniteRecherche.
     *
     * @return UniteRecherche|null
     */
    public function getUniteRecherche()
    {
        return $this->uniteRecherche;
    }

    /**
     * Set etablissementLaboratoireRecherche.
     *
     * @param string|null $etablissementLaboratoireRecherche
     *
     * @return Inscription
     */
    public function setEtablissementLaboratoireRecherche($etablissementLaboratoireRecherche = null)
    {
        $this->etablissementLaboratoireRecherche = $etablissementLaboratoireRecherche;

        return $this;
    }

    /**
     * Get etablissementLaboratoireRecherche.
     *
     * @return string|null
     */
    public function getEtablissementLaboratoireRecherche()
    {
        return $this->etablissementLaboratoireRecherche;
    }

    /**
     * Set etablissementInscription.
     *
     * @param Etablissement|null $etablissementInscription
     *
     * @return Inscription
     */
    public function setEtablissementInscription(Etablissement $etablissementInscription = null)
    {
        $this->etablissementInscription = $etablissementInscription;

        return $this;
    }

    /**
     * Get etablissementInscription.
     *
     * @return Etablissement|null
     */
    public function getEtablissementInscription()
    {
        return $this->etablissementInscription;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return Inscription
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
     * Set specialiteDoctorat.
     *
     * @param Discipline|null $specialiteDoctorat
     *
     * @return Inscription
     */
    public function setSpecialiteDoctorat(Discipline $specialiteDoctorat = null)
    {
        $this->specialiteDoctorat = $specialiteDoctorat;

        return $this;
    }

    /**
     * Get specialiteDoctorat.
     *
     * @return Discipline|null
     */
    public function getSpecialiteDoctorat()
    {
        return $this->specialiteDoctorat;
    }

    /**
     * Set prenomDirecteurThese.
     *
     * @param string|null $prenomDirecteurThese
     *
     * @return Inscription
     */
    public function setPrenomDirecteurThese($prenomDirecteurThese = null)
    {
        $this->prenomDirecteurThese = $prenomDirecteurThese;

        return $this;
    }

    /**
     * Get prenomDirecteurThese.
     *
     * @return string|null
     */
    public function getPrenomDirecteurThese()
    {
        return $this->prenomDirecteurThese;
    }

    /**
     * Set prenomCodirecteurThese.
     *
     * @param string|null $prenomCodirecteurThese
     *
     * @return Inscription
     */
    public function setPrenomCodirecteurThese($prenomCodirecteurThese = null)
    {
        $this->prenomCodirecteurThese = $prenomCodirecteurThese;

        return $this;
    }

    /**
     * Get prenomCodirecteurThese.
     *
     * @return string|null
     */
    public function getPrenomCodirecteurThese()
    {
        return $this->prenomCodirecteurThese;
    }

    /**
     * Set directeur.
     *
     * @param Individu|null $directeur
     *
     * @return Inscription
     */
    public function setDirecteur(Individu $directeur = null)
    {
        $this->directeur = $directeur;

        return $this;
    }

    /**
     * Get directeur.
     *
     * @return Individu|null
     */
    public function getDirecteur()
    {
        return $this->directeur;
    }

    /**
     * Set coDirecteur.
     *
     * @param Individu|null $coDirecteur
     *
     * @return Inscription
     */
    public function setCoDirecteur(Individu $coDirecteur = null)
    {
        $this->coDirecteur = $coDirecteur;

        return $this;
    }

    /**
     * Get coDirecteur.
     *
     * @return Individu|null
     */
    public function getCoDirecteur()
    {
        return $this->coDirecteur;
    }

    /**
     * Set emailDirecteurThese.
     *
     * @param string|null $emailDirecteurThese
     *
     * @return Inscription
     */
    public function setEmailDirecteurThese($emailDirecteurThese = null)
    {
        $this->emailDirecteurThese = $emailDirecteurThese;

        return $this;
    }

    /**
     * Get emailDirecteurThese.
     *
     * @return string|null
     */
    public function getEmailDirecteurThese()
    {
        return $this->emailDirecteurThese;
    }

    /**
     * Set emailCodirecteurThese.
     *
     * @param string|null $emailCodirecteurThese
     *
     * @return Inscription
     */
    public function setEmailCodirecteurThese($emailCodirecteurThese = null)
    {
        $this->emailCodirecteurThese = $emailCodirecteurThese;

        return $this;
    }

    /**
     * Get emailCodirecteurThese.
     *
     * @return string|null
     */
    public function getEmailCodirecteurThese()
    {
        return $this->emailCodirecteurThese;
    }

    /**
     * Get verificationInscription.
     *
     * @return Collection
     */
    public function getVerificationInscription(): Collection
    {
        return $this->verificationInscription;
    }

    /**
     * Add VerificationInscription.
     */
    public function addVerificationInscription(Collection $verificationInscriptions)
    {
//        foreach ($verificationInscriptions as $vI) {
//            if (!$this->verificationInscription->contains($vI)) {
//                $this->verificationInscription->add($vI);
//            }
//        }

        return $this;
    }

    /**
     * Remove VerificationInscription.
     */
    public function removeVerificationInscription(Collection $verificationInscriptions)
    {
        foreach ($verificationInscriptions as $vI) {
            $this->verificationInscription->removeElement($vI);
        }
    }

    /**
     * Set uniteRechercheCoDirecteur.
     *
     * @param UniteRecherche|null $uniteRechercheCoDirecteur
     *
     * @return Inscription
     */
    public function setUniteRechercheCoDirecteur(UniteRecherche $uniteRechercheCoDirecteur = null)
    {
        $this->uniteRechercheCoDirecteur = $uniteRechercheCoDirecteur;

        return $this;
    }

    /**
     * Get uniteRechercheCoDirecteur.
     *
     * @return UniteRecherche|null
     */
    public function getUniteRechercheCoDirecteur()
    {
        return $this->uniteRechercheCoDirecteur;
    }

    /**
     * Set etablissementRattachementCoDirecteur.
     *
     * @param Etablissement|null $etablissementRattachementCoDirecteur
     *
     * @return Inscription
     */
    public function setEtablissementRattachementCoDirecteur(Etablissement $etablissementRattachementCoDirecteur = null)
    {
        $this->etablissementRattachementCoDirecteur = $etablissementRattachementCoDirecteur;

        return $this;
    }

    /**
     * Get etablissementRattachementCoDirecteur.
     *
     * @return Etablissement|null
     */
    public function getEtablissementRattachementCoDirecteur()
    {
        return $this->etablissementRattachementCoDirecteur;
    }

    /**
     * Set fonctionDirecteurThese.
     *
     * @param Qualite|null $fonctionDirecteurThese
     *
     * @return Inscription
     */
    public function setFonctionDirecteurThese(Qualite $fonctionDirecteurThese = null)
    {
        $this->fonctionDirecteurThese = $fonctionDirecteurThese;

        return $this;
    }

    /**
     * Get fonctionDirecteurThese.
     *
     * @return Qualite|null
     */
    public function getFonctionDirecteurThese()
    {
        return $this->fonctionDirecteurThese;
    }

    /**
     * Set fonctionCoDirecteurThese.
     *
     * @param Qualite|null $fonctionCoDirecteurThese
     *
     * @return Inscription
     */
    public function setFonctionCoDirecteurThese(Qualite $fonctionCoDirecteurThese = null)
    {
        $this->fonctionCoDirecteurThese = $fonctionCoDirecteurThese;

        return $this;
    }

    /**
     * Get fonctionCoDirecteurThese.
     *
     * @return Qualite|null
     */
    public function getFonctionCoDirecteurThese()
    {
        return $this->fonctionCoDirecteurThese;
    }

    public function getUniteRechercheCoDirecteurLibelle()
    {
        return $this->getUniteRechercheCoDirecteur()?->getStructure()->getLibelle();
    }

    public function getEtablissementRattachementCoDirecteurLibelle()
    {
        return $this->getEtablissementRattachementCoDirecteur()?->getStructure()->getLibelle();
    }

    public function getFonctionDirecteurLibelle(): ?string
    {
        return $this->getFonctionDirecteurThese()?->getLibelle();
    }

    public function getFonctionCoDirecteurLibelle(): ?string
    {
        return $this->getFonctionCoDirecteurThese()?->getLibelle();
    }

    public function getDenominationDirecteurThese() : ?string
    {
        return ($this->getNomDirecteurThese() || $this->getPrenomDirecteurThese()) ? $this->getNomDirecteurThese()." ".$this->getPrenomDirecteurThese() : null;
    }
}
