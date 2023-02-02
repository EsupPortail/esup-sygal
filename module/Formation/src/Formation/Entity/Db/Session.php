<?php

namespace Formation\Entity\Db;

use Doctorant\Entity\Db\Doctorant;
use Individu\Entity\Db\Individu;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Formation\Entity\Db\Interfaces\HasModaliteInterface;
use Formation\Entity\Db\Interfaces\HasSiteInterface;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Traits\HasModaliteTrait;
use Formation\Entity\Db\Traits\HasSiteTrait;
use Formation\Entity\Db\Traits\HasTypeTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Session implements HistoriqueAwareInterface,
    HasSiteInterface, HasModaliteInterface, HasTypeInterface {
    use HistoriqueAwareTrait;
    use HasSiteTrait;
    use HasModaliteTrait;
    use HasTypeTrait;

    const ETAT_PREPARATION       = 'P';
    const ETAT_INSCRIPTION       = 'O';
    const ETAT_INSCRIPTION_CLOSE = 'F';
    const ETAT_IMMINENTE         = 'I';
    const ETAT_EN_COURS          = 'E';
    const ETAT_CLOS_FINAL        = 'C';
    const ETATS = [
        self::ETAT_PREPARATION => self::ETAT_PREPARATION,
        self::ETAT_INSCRIPTION => self::ETAT_INSCRIPTION,
        self::ETAT_INSCRIPTION_CLOSE => self::ETAT_INSCRIPTION_CLOSE,
        self::ETAT_IMMINENTE => self::ETAT_IMMINENTE,
        self::ETAT_EN_COURS => self::ETAT_EN_COURS,
        self::ETAT_CLOS_FINAL => self::ETAT_CLOS_FINAL,
    ];

    const INTERVAL_ENQUETE = 'P1M';

    private int $id;
    private ?int $index = null;
    private ?Formation $formation = null;

    private ?Individu $responsable = null;
    private ?Etat $etat = null;
    private ?string $description = null;
    private ?DateTime $dateClotureInscription = null;

    private Collection $structuresValides;
    private Collection $seances;
    private Collection $formateurs;
    private Collection $inscriptions;
    private Collection $heurodatages;

    private ?int $tailleListePrincipale = null;
    private ?int $tailleListeComplementaire = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function getResponsable(): ?Individu
    {
        return $this->responsable;
    }

    public function setResponsable(?Individu $responsable): void
    {
        $this->responsable = $responsable;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): void
    {
        $this->etat = $etat;
    }

    public function getStructuresValides(): Collection
    {
        return $this->structuresValides;
    }

    public function getSeances() : Collection
    {
        return $this->seances;
    }

    public function getFormateurs() : Collection
    {
        return $this->formateurs;
    }

    public function getInscriptions() : Collection
    {
        return $this->inscriptions;
    }

    public function getInscriptionsByListe(?string $liste) : array
    {
        $result = array_filter($this->inscriptions->toArray(), function (Inscription $a) use ($liste) { return ($a->estNonHistorise() AND $a->getListe()===$liste);});
        return $result;
    }

    public function getTailleListePrincipale(): ?int
    {
        return $this->tailleListePrincipale;
    }

    public function setTailleListePrincipale(?int $tailleListePrincipale): void
    {
        $this->tailleListePrincipale = $tailleListePrincipale;
    }

    public function getListePrincipale() : array
    {
        $inscriptions = array_filter($this->getInscriptions()->toArray(), function (Inscription $a) { return $a->estNonHistorise() AND $a->getListe() === Inscription::LISTE_PRINCIPALE; });
        return $inscriptions;
    }

    public function isListePrincipaleComplete() : bool
    {
        $inscrit = $this->getListePrincipale();
        return count($inscrit) >= ($this->getTailleListePrincipale()??0);
    }

    public function getTailleListeComplementaire(): ?int
    {
        return $this->tailleListeComplementaire;
    }

    public function setTailleListeComplementaire(?int $tailleListeComplementaire): void
    {
        $this->tailleListeComplementaire = $tailleListeComplementaire;
    }

    public function getListeComplementaire() : array
    {
        $inscriptions = array_filter($this->getInscriptions()->toArray(), function (Inscription $a) { return $a->estNonHistorise() AND $a->getListe() === Inscription::LISTE_COMPLEMENTAIRE; });
        return $inscriptions;
    }

    public function isListeComplementaireComplete() : bool
    {
        $inscrit = $this->getListeComplementaire();
        return count($inscrit) >= ($this->getTailleListeComplementaire()??0);
    }

    public function getNonClasses() : array
    {
        $inscriptions = array_filter($this->getInscriptions()->toArray(), function (Inscription $a) { return $a->estNonHistorise() AND $a->getListe() === null; });
        return $inscriptions;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDateClotureInscription(): ?DateTime
    {
        return $this->dateClotureInscription;
    }

    public function setDateClotureInscription(?DateTime $dateClotureInscription): void
    {
        $this->dateClotureInscription = $dateClotureInscription;
    }

    public function getDuree() : float
    {
        $somme = new DateTime('00:00');
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise()) {
                $debut = $seance->getDebut();
                $fin = $seance->getFin();
                $interval = $debut->diff($fin);
                $somme->add($interval);
            }
        }
        $interval = $somme->diff(new DateTime('00:00'));
        return ((float) $interval->format('%d')*24 + (float) $interval->format('%h')) + ((float) $interval->format('%i'))/60;
    }

    public function estTerminee() : bool
    {
        $maintenant = new DateTime();
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise() AND $seance->getFin() > $maintenant) return false;
        }
        return true;
    }

    public function getDateDebut() : ?DateTime
    {
        $debut = null;
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise()) {
                if ($debut === null or $seance->getDebut() < $debut) $debut = $seance->getDebut();
            }
        }
        return $debut;
    }

    public function getDateFin() : ?DateTime
    {
        $fin = null;
        /** @var Seance $seance */
        foreach ($this->getSeances() as $seance) {
            if ($seance->estNonHistorise()) {
                if ($fin === null or $seance->getFin() > $fin) $fin = $seance->getFin();
            }
        }
        return $fin;
    }

    public function getLimiteInscription() : ?DateTime
    {
        $debut = $this->getDateDebut();
        if ($debut === null) return null;

        $limite = DateTime::createFromFormat('d/m/Y', $debut->format('d/m/y'));
        $limite->sub(new DateInterval('P14D'));
        return $limite;
    }

    public function estInscrit(Doctorant $doctorant) : bool
    {
        /** @var Inscription $inscription */
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->estNonHistorise() AND $inscription->getDoctorant() === $doctorant) return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getCode() : string
    {
        $formation = $this->getFormation();
        $module = $formation->getModule();
        return 'M'.$module->getId() . 'F'.$formation->getId() . 'S'.$this->getId();
    }

    public function getAnneeScolaire() : int
    {
        $debut = $this->getDateDebut();
        if ($debut === null) $debut = new DateTime();

        $mois = ((int) $debut->format('m'));
        $annee =  ((int) $debut->format('Y'));
        if ($mois > 8) $annee += 1;
        return $annee;
    }

    /**
     * @return SessionEtatHeurodatage[]
     */
    public function getHeurodatages() : array
    {
        $array = $this->heurodatages->toArray();
        usort($array,
            function (SessionEtatHeurodatage $a, SessionEtatHeurodatage $b) {
                return $a->getHeurodatage() > $b->getHeurodatage(); });
        return $array;
    }

    /** Pour les macros ********************************************************************************/

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getPeriode() : string {
        $jour_debut = $this->getDateDebut()->format('d/m/Y');
        $jour_fin = $this->getDateFin()->format('d/m/Y');

        if ($jour_debut === $jour_fin) return $jour_debut;
        return $jour_debut." au ".$jour_fin;
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getSeancesAsTable() : string
    {
        $seances = $this->getSeances()->toArray();
        $seances = array_filter($seances, function(Seance $a) { return $a->estNonHistorise();});
        usort($seances, function (Seance $a, Seance $b) { return $a->getDebut() > $b->getDebut(); });

        if (empty($seances)) return "Aucune séance d'associée à cette session de formation.";

        $texte  = '<table>';
        $texte .= '<thead><tr>';
        $texte .= '<th> Jour de la séance </th><th> Heure de début </th><th> Heure de fin </th><th> Lieu </th>';
        $texte .= '</tr></thead>';
        $texte .= '<tbody>';
        /** @var Seance $seance */
        foreach ($seances as $seance) {
            $texte .= '<tr>';
            $texte .= '<td>'.$seance->getDebut()->format('d/m/Y').'</td>';
            $texte .= '<td>'.$seance->getDebut()->format('H:i').'</td>';
            $texte .= '<td>'.$seance->getFin()->format('H:i').'</td>';
            $texte .= '<td>'.$seance->getLieu().'</td>';
            $texte .= '</tr>';
        }
        $texte .= '</tbody>';
        $texte .= '</table>';
        return $texte;
    }

    public function isFinInscription() : bool
    {
        $etatCode = $this->getEtat()->getCode();
        return ($etatCode === Session::ETAT_INSCRIPTION_CLOSE OR $etatCode === Session::ETAT_IMMINENTE);
    }

    public function getDenominationResponsable() : string
    {
        $responsable = $this->getResponsable();
        return $responsable?$responsable->getNomComplet():"Aucun responsable de désigner pour cette session";
    }
}