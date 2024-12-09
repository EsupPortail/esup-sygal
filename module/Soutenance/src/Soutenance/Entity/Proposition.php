<?php

namespace Soutenance\Entity;

use Doctrine\Common\Collections\Collection;
use Horodatage\Entity\Interfaces\HasHorodatagesInterface;
use Horodatage\Entity\Traits\HasHorodatagesTrait;
use RuntimeException;
use JetBrains\PhpStorm\Pure;
use These\Entity\Db\These;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;

class Proposition implements HistoriqueAwareInterface, HasHorodatagesInterface {
    use HistoriqueAwareTrait;
    use HasHorodatagesTrait;

    private ?int $id = null;
    private ?These $these = null;
    private ?DateTime $date = null;
    private ?string $lieu = null;
    private ?string $adresse = null;
    private Collection $adresses;
    private bool $exterieur = false;

    private Collection $membres;

    private ?DateTime $renduRapport = null;
    private ?DateTime $confidentialite = null;
    private bool $huitClos = false;
    private bool $labelEuropeen = false;
//    private bool $manuscritAnglais = false;
    private bool $soutenanceAnglais = false;
    private ?string $nouveauTitre = null;
    private  ?Etat $etat = null;
    private ?string $sursis = null;

    private Collection $justificatifs;
    private Collection $avis;

    public function __construct(?These $these = null)
    {
        $this->membres = new ArrayCollection();

        $this->setThese($these);
        $this->setLabelEuropeen(false);
//        $this->setManuscritAnglais(false);
        $this->setSoutenanceAnglais(false);
        $this->setHuitClos(false);
        $this->setExterieur(false);

        $this->justificatifs = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->adresses = new ArrayCollection();
        $this->horodatages = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getThese() : ?These
    {
        return $this->these;
    }

    public function setThese(These $these) : void
    {
        $this->these = $these;
    }

    public function getDate() : ?DateTime
    {
        return $this->date;
    }

    public function setDate(?DateTime $date) : void
    {
        $this->date = $date;
    }

    public function getLieu() : ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu) : void
    {
        $this->lieu = $lieu;
    }

    public function getAdresse() : ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse) : void
    {
        $this->adresse = $adresse;
    }

    public function isExterieur() : bool
    {
        return $this->exterieur;
    }

    public function setExterieur(bool $exterieur) : void
    {
        $this->exterieur = $exterieur;
    }

    public function getMembres()
    {
        return $this->membres;
    }

    /**
     * @return DateTime
     */
    public function getConfidentialite()
    {
        return $this->confidentialite;
    }

    /**
     * @param DateTime $confidentialite
     * @return Proposition
     */
    public function setConfidentialite($confidentialite)
    {
        $this->confidentialite = $confidentialite;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHuitClos()
    {
        return $this->huitClos;
    }

    /**
     * @param bool $huitClos
     * @return Proposition
     */
    public function setHuitClos($huitClos)
    {
        $this->huitClos = $huitClos;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLabelEuropeen()
    {
        return $this->labelEuropeen;
    }

    /**
     * @param bool $labelEuropeen
     * @return Proposition
     */
    public function setLabelEuropeen($labelEuropeen)
    {
        $this->labelEuropeen = $labelEuropeen;
        return $this;
    }

//    /**
//     * @return bool
//     */
//    public function isManuscritAnglais()
//    {
//        return $this->manuscritAnglais;
//    }
//
//    /**
//     * @param bool $manuscritAnglais
//     * @return Proposition
//     */
//    public function setManuscritAnglais($manuscritAnglais)
//    {
//        $this->manuscritAnglais = $manuscritAnglais;
//        return $this;
//    }

    /**
     * @return bool
     */
    public function isSoutenanceAnglais()
    {
        return $this->soutenanceAnglais;
    }

    /**
     * @param bool $soutenanceAnglais
     * @return Proposition
     */
    public function setSoutenanceAnglais($soutenanceAnglais)
    {
        $this->soutenanceAnglais = $soutenanceAnglais;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRenduRapport()
    {
        return $this->renduRapport;
    }

    /**
     * @param DateTime $renduRapport
     * @return Proposition
     */
    public function setRenduRapport($renduRapport)
    {
        $this->renduRapport = $renduRapport;
        return $this;
    }

    /** @return boolean */
    public function hasDateEtLieu()
    {
        return ($this->getDate() && $this->getLieu());
    }


    /**
     * @return Membre[]
     */
    public function getRapporteurs()
    {
        $rapporteurs = [];
        $membres = $this->getMembres();
        /** @var Membre $membre */
        foreach ($membres as $membre) {
            if ($membre->estRapporteur()) $rapporteurs[] = $membre;
        }

        return $rapporteurs;
    }

    /**
     * @return string
     */
    public function getNouveauTitre()
    {
        return $this->nouveauTitre;
    }

    /**
     * @param string $nouveauTitre
     * @return Proposition
     */
    public function setNouveauTitre($nouveauTitre)
    {
        $this->nouveauTitre = $nouveauTitre;
        return $this;
    }

    /**
     * @return Justificatif[]
     */
    public function getJustificatifs()
    {
        return $this->justificatifs->toArray();
    }

    /**
     * @param string $nature
     * @param null $membre
     * @return Justificatif|null
     */
    public function getJustificatif($nature, $membre = null) {
        /** @var Justificatif $justificatif */
        foreach ($this->justificatifs as $justificatif) {
            if (($membre === null OR $justificatif->getMembre() === $membre) AND
                $justificatif->getFichier()->getFichier()->getNature()->getCode() === $nature AND
                $justificatif->estNonHistorise()
            ) {
                return $justificatif;
            }
        }
        return null;
    }

    /**
     * @param Justificatif $justificatif
     * @return Proposition
     */
    public function addJustificatif($justificatif)
    {
        $this->justificatifs->add($justificatif);
        return $this;
    }

    public function removeJustificatif($justificatif)
    {
        $this->justificatifs->removeElement($justificatif);
        return $this;
    }

    /**
     * @return Etat
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param Etat $etat
     * @return Proposition
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
        return $this;
    }

    /**
     * @var bool $sursis
     * @return Proposition
     */
    public function setSurcis($sursis)
    {
        if ($sursis) {
            $this->sursis = 'O';
        } else {
            $this->sursis = 'N';
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSursis()
    {
        return ($this->sursis === 'O');
    }


    /**
     * @return ArrayCollection
     */
    public function getAvis()
    {
        return $this->avis;
    }

    /**
     * @param ArrayCollection $avis
     * @return Proposition
     */
    public function setAvis($avis)
    {
        $this->avis = $avis;
        return $this;
    }

    /**
     * retour vrai si un début de saisie a été fait (date/lieu)
     * @return bool
     */
    public function hasSaisie() : bool
    {
        if ($this->getDate() OR $this->getLieu())  return true;
        return false;
    }

    /**
     * @return bool
     */
    public function hasVisio() : bool
    {
        /** @var Membre $membre */
        foreach ($this->membres as $membre) {
            if ($membre->isVisio()) return true;
        }
        return false;
    }

    /** Adresse ********************************/

    public function getAdresseActive() : ?Adresse
    {
        $result = null;
        /** @var Adresse $adresse */
        foreach ($this->adresses as $adresse) {
            if ($adresse->estNonHistorise()) {
                if ($result === null) {
                    $result = $adresse;
                } else {
                    throw new RuntimeException("Plusieurs [".Adresse::class."] sont actives pour la proposition [".$this->getId()."]");
                }
            }
        }
        return $result;
    }

    /** FONCTIONS POUR LES MACROS *************************************************************************************/

    /** @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \These\Renderer\TheseRendererAdapter} */
    public function toStringDateRetourRapport() : string
    {
        $date = $this->getRenduRapport();
        if ($date) return $date->format('d/m/Y');
        return "<span style='color:darkorange;'>Aucune date de rendu de précisée</span>";
    }

    /** @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter} */
    public function toStringDateSoutenance() : string
    {
        $date = $this->getDate();
        if ($date) return $date->format('d/m/Y à H:i');
        return "<span style='color:darkorange;'>Aucune date de rendu de précisée</span>";
    }

    /** @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter} */
    #[Pure] public function toStringLieu() : string
    {
        $lieu = $this->getLieu();
        if ($lieu) return $lieu;
        return "<span style='color:darkorange;'>Aucun lieu de précisé</span>";
    }

    /** @noinspection  PhpUnused Utilisé par la macro Soutenance#Adresse
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter} */
    #[Pure] public function toStringAdresse() : string
    {
        $lieu = $this->getAdresseActive();
        if ($lieu) return $lieu->format();
        return "<span style='color:darkorange;'>Aucune adresse de précisée</span>";
    }

    /** @noinspection  PhpUnused
     * @deprecated A supprimer lorqu'aura été généralisée l'utilisation de {@see \Soutenance\Renderer\PropositionSoutenanceRendererAdapter}*/
    #[Pure] public function toStringPublicOuHuisClos() : string
    {
        $mode = $this->isHuitClos();
        if ($mode === false) return " sera publique ";
        if ($mode === true) return " se déroulera en huis clos";
        return "<span style='color:darkorange;'>Aucun mode de déclaré</span>";
    }
}